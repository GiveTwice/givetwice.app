@props(['status'])

@if($status === 'completed')
    <span class="badge badge-success text-xs">Completed</span>
@elseif($status === 'pending' || $status === 'fetching')
    <span class="badge badge-warning text-xs">{{ ucfirst($status) }}</span>
@elseif($status === 'failed')
    <span class="badge badge-danger text-xs">Failed</span>
@endif
