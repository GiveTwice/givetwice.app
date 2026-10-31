<?php

use App\Actions\FetchGiftDetailsAction;
use App\Actions\ProcessGiftImageAction;
use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use GiveTwice\ProductInfoFetcher\DataTransferObjects\ProductInfo;
use GiveTwice\ProductInfoFetcher\Enum\FetchOutcome;
use GiveTwice\ProductInfoFetcher\ProductInfoFetcher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Queue::fake();
    $this->user = User::factory()->create();
    $this->list = GiftList::factory()->create(['creator_id' => $this->user->id]);
    $this->user->lists()->attach($this->list);
    $this->actingAs($this->user);
});

it('keeps the timeout reachable across repeated polling requests', function () {
    $gift = Gift::factory()->pending()->create(['user_id' => $this->user->id]);
    foreach ([5, 5, 5, 5, 5] as $seconds) {
        $this->travel($seconds)->seconds();
        $this->get("/en/gifts/{$gift->id}/fetching")->assertOk();
    }
    $this->travel(5)->seconds();
    $this->get("/en/gifts/{$gift->id}/fetching")
        ->assertRedirect("/en/gifts/{$gift->id}/edit")
        ->assertSessionHas('gift_context', 'fetch_timeout');
});

it('prefills a readable title and shop from retailer URLs', function (string $url, string $title, string $shop) {
    $gift = Gift::factory()->pending()->create(['user_id' => $this->user->id, 'url' => $url, 'title' => null]);
    expect($gift->suggestedTitle())->toBe($title);
    expect($gift->siteName())->toBe($shop);
    $this->get("/en/gifts/{$gift->id}/edit?context=fetch_failed")->assertOk()->assertSee($title)->assertSee($shop);
})->with([
    ['https://example.com/wallet', 'Wallet', 'example.com'],
    ['https://www.bol.com/nl/nl/p/lego-botanicals-gezellige-plantjes/9300000123456/', 'Lego botanicals gezellige plantjes', 'bol.com'],
    ['https://www.zalando.be/rayi-small-wallet-beige-ab1234.html', 'Rayi small wallet beige', 'zalando.be'],
    ['https://www.etsy.com/listing/123456/gold-hoop-earrings', 'Gold hoop earrings', 'etsy.com'],
    ['https://www.nike.com/be/t/air-max-shoes-abc123/DM1234-100', 'Air max shoes', 'nike.com'],
    ['https://eur.shein.com/Floral-Summer-Dress-p-123456.html', 'Floral Summer Dress', 'eur.shein.com'],
]);

it('saves just a name and shows the share step once after the first gift', function () {
    $this->post('/en/gifts', ['input' => 'A nice book', 'list_id' => $this->list->id])->assertRedirect();
    $gift = $this->user->gifts()->sole();
    $this->get("/en/gifts/{$gift->id}/edit")->assertOk();
    $this->put("/en/gifts/{$gift->id}", ['title' => 'A nice book'])
        ->assertSessionHasNoErrors()->assertRedirect("/en/gifts/{$gift->id}/share");
    $this->get("/en/gifts/{$gift->id}/share")->assertOk()->assertViewIs('gifts.share')->assertSee('https://wa.me/?text=', false);
    $this->put("/en/gifts/{$gift->id}", ['title' => 'Still a nice book'])->assertRedirect('/en/dashboard');
    $this->post('/en/gifts', ['input' => 'AVA gift voucher', 'list_id' => $this->list->id]);
    $second = $this->user->gifts()->latest('id')->first();
    $this->put("/en/gifts/{$second->id}", ['title' => 'AVA gift voucher'])->assertRedirect('/en/dashboard');
    $this->get("/en/gifts/{$second->id}/share")->assertRedirect('/en/dashboard');
});

it('saves and shares a pending link without waiting for its fetch', function () {
    $this->post('/en/gifts', ['input' => 'https://www.bol.com/nl/p/lego-plantjes/123', 'list_id' => $this->list->id]);
    $gift = $this->user->gifts()->sole();
    $this->put("/en/gifts/{$gift->id}", ['title' => 'Lego plantjes', 'owner_note' => 'For the windowsill'])
        ->assertRedirect("/en/gifts/{$gift->id}/share");
    expect($gift->fresh())->owner_note->toBe('For the windowsill')->fetch_status->toBe('pending');
});

it('protects edited fields including intentional blanks from a fetch already in flight', function (FetchOutcome $outcome) {
    Storage::fake('public');
    $gift = Gift::factory()->pending()->create(['user_id' => $this->user->id, 'title' => null, 'description' => null, 'price_in_cents' => null]);
    $fetcher = Mockery::mock(ProductInfoFetcher::class);
    $fetcher->shouldReceive('fetchResult')->once()->andReturnUsing(function () use ($gift, $outcome) {
        $this->put("/en/gifts/{$gift->id}", [
            'title' => 'My title', 'description' => '', 'price' => '12.50', 'currency' => 'EUR',
            'edited_fields' => ['title', 'description', 'price_in_cents', 'currency'],
        ])->assertSessionHasNoErrors();

        $this->postJson("/en/gifts/{$gift->id}/upload-image", ['image' => UploadedFile::fake()->image('mine.jpg')])->assertSuccessful();

        return new ProductInfo(outcome: $outcome, imageUrl: 'https://example.com/image.jpg', allImageUrls: ['https://example.com/image.jpg'], name: 'Scraped title', description: 'Scraped description', priceInCents: 9999, priceCurrency: 'USD', rating: 4.5);
    });
    $images = Mockery::mock(ProcessGiftImageAction::class);
    $images->shouldNotReceive('fromUrl');
    $images->shouldReceive('dispatchCompletedEvent');
    (new FetchGiftDetailsAction($gift))->setFetcher($fetcher)->handle($images);
    expect($gift->fresh()->getFirstMedia('image')->file_name)->toBe('mine.jpg');
    expect($gift->fresh())->title->toBe('My title')->description->toBeNull()->price_in_cents->toBe(1250)->currency->toBe('EUR')->rating->toBe('4.5')
        ->fetch_status->toBe($outcome === FetchOutcome::Success ? 'completed' : 'failed');
})->with([FetchOutcome::Success, FetchOutcome::Consent]);

it('fills untouched fields on a late fetch', function (FetchOutcome $outcome) {
    $gift = Gift::factory()->pending()->create(['user_id' => $this->user->id, 'description' => null, 'price_in_cents' => null]);
    $this->put("/en/gifts/{$gift->id}", ['title' => 'My wish']);
    $fetcher = Mockery::mock(ProductInfoFetcher::class);
    $fetcher->shouldReceive('fetchResult')->andReturn(new ProductInfo(outcome: $outcome, imageUrl: 'https://example.com/image.jpg', name: 'Imported', description: 'Details', priceInCents: 1500));
    $images = Mockery::mock(ProcessGiftImageAction::class);
    $images->shouldReceive('dispatchCompletedEvent');
    (new FetchGiftDetailsAction($gift))->setFetcher($fetcher)->handle($images);
    expect($gift->fresh())->title->toBe('My wish')->description->toBe('Details')->price_in_cents->toBe(1500)
        ->fetch_status->toBe($outcome === FetchOutcome::Success ? 'completed' : 'failed');
    if ($outcome === FetchOutcome::Consent) {
        $this->get("/en/gifts/{$gift->id}/fetching")->assertRedirect("/en/gifts/{$gift->id}/edit")->assertSessionHas('gift_context', 'fetch_failed');
        $this->get("/en/gifts/{$gift->id}/edit")->assertOk()->assertSee('My wish')->assertSee('Details');
    }
})->with([FetchOutcome::Success, FetchOutcome::Consent]);

it('records fetch outcomes independently of surviving gift records', function (bool $succeeds) {
    $gift = Gift::factory()->pending()->create(['user_id' => $this->user->id, 'url' => 'https://www.zalando.be/wallet']);
    $fetcher = Mockery::mock(ProductInfoFetcher::class);
    if ($succeeds) {
        $fetcher->shouldReceive('fetchResult')->andReturn(new ProductInfo(outcome: FetchOutcome::Success, name: 'Wallet'));
    } else {
        $fetcher->shouldReceive('fetchResult')->andReturn(new ProductInfo(outcome: FetchOutcome::Blocked, httpStatus: 403));
    }
    $images = Mockery::mock(ProcessGiftImageAction::class);
    $images->shouldReceive('dispatchCompletedEvent');
    Log::spy();
    (new FetchGiftDetailsAction($gift))->setFetcher($fetcher)->handle($images);
    $gift->delete();
    Log::shouldHaveReceived('info')->with('Gift fetch attempt', Mockery::on(fn (array $data): bool => $data['host'] === 'zalando.be'
        && $data['status'] === ($succeeds ? 'completed' : 'failed') && is_int($data['duration_ms']) && $data['duration_ms'] >= 0))->once();
})->with([true, false]);

it('lets a late fetch improve an untouched slug prefill', function () {
    $gift = Gift::factory()->pending()->create(['user_id' => $this->user->id, 'title' => null]);
    $this->put("/en/gifts/{$gift->id}", ['title' => 'Lego plantjes', 'tracks_edits' => true])->assertSessionHasNoErrors();
    expect($gift->fresh()->edited_fields)->toBe([]);
    $fetcher = Mockery::mock(ProductInfoFetcher::class);
    $fetcher->shouldReceive('fetchResult')->andReturn(new ProductInfo(outcome: FetchOutcome::Success, name: 'LEGO Botanicals 10329'));
    $images = Mockery::mock(ProcessGiftImageAction::class);
    $images->shouldReceive('dispatchCompletedEvent');
    (new FetchGiftDetailsAction($gift))->setFetcher($fetcher)->handle($images);
    expect($gift->fresh()->title)->toBe('LEGO Botanicals 10329');
});

it('keeps an uploaded photo when a fetch later offers an image', function () {
    Storage::fake('public');
    $gift = Gift::factory()->pending()->create(['user_id' => $this->user->id]);
    $this->postJson("/en/gifts/{$gift->id}/upload-image", ['image' => UploadedFile::fake()->image('wallet.jpg')])->assertSuccessful();
    $mediaId = $gift->fresh()->getFirstMedia('image')->id;
    expect(app(ProcessGiftImageAction::class)->fromUrl($gift, 'https://example.com/scraped.jpg'))->toBeFalse();
    expect($gift->fresh()->getFirstMedia('image')->id)->toBe($mediaId);
});

it('finishes challenge or empty pages with the friendly fallback', function (?string $title) {
    $gift = Gift::factory()->pending()->create(['user_id' => $this->user->id, 'title' => null]);
    $fetcher = Mockery::mock(ProductInfoFetcher::class);
    $fetcher->shouldReceive('fetchResult')->once()->andReturn(new ProductInfo(outcome: $title === 'Access denied' ? FetchOutcome::Blocked : FetchOutcome::Incomplete, name: $title));
    $images = Mockery::mock(ProcessGiftImageAction::class);
    Log::spy();
    $images->shouldReceive('dispatchCompletedEvent')->once();
    (new FetchGiftDetailsAction($gift))->setFetcher($fetcher)->handle($images);
    Log::shouldHaveReceived('info')->with('Gift fetch attempt', Mockery::on(fn (array $data): bool => $data['status'] === 'failed'))->once();
    expect($gift->fresh())->title->toBeNull()->fetch_status->toBe('failed');
})->with([null, 'Access denied', "Women's & Men's Clothing, Shop Online Fashion | SHEIN"]);
