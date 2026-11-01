@props(['list'])
@php($publicUrl = $list->getPublicUrl())
<div x-data="{
    copied: false,
    async copy() {
        try { await navigator.clipboard.writeText(@js($publicUrl)); this.copied = true; }
        catch { $refs.link.hidden = false; $refs.link.select(); }
    },
    async more() {
        try { await navigator.share({ title: @js($list->name), url: @js($publicUrl) }); }
        catch (error) { if (error.name !== 'AbortError') this.copy(); }
    }
}">
    <a href="{{ \App\Helpers\ListShareHelper::whatsappUrl($list) }}" target="_blank" rel="noopener" class="flex w-full items-center justify-center rounded-xl bg-green-700 px-4 py-3 font-medium text-white">{{ __('Send via WhatsApp') }}</a>
    <div class="mt-3 flex gap-3">
        <button type="button" @click="copy()" class="btn-secondary flex-1 justify-center"><span x-text="copied ? @js(__('Copied!')) : @js(__('Copy link'))"></span></button>
        <button x-show="!!navigator.share" @click="more()" class="btn-secondary flex-1 justify-center">{{ __('More...') }}</button>
    </div>
    <input x-ref="link" hidden readonly value="{{ $publicUrl }}" class="form-input mt-3" aria-label="{{ __('Public Link') }}">
</div>
