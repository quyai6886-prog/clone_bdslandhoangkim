@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="dashboard-toolbar">
    <div><strong>{{ number_format($properties->total(), 0, ',', '.') }}</strong> căn phù hợp <span class="muted">· Trang {{ $properties->currentPage() }}/{{ $properties->lastPage() }}</span></div>
    <div class="toolbar-actions">
        @if(request()->query())<a class="btn ghost" href="{{ route('dashboard') }}">Xóa bộ lọc</a>@endif
        <button class="btn primary" type="button" data-open-filter>⚙ Bộ lọc</button>
    </div>
</div>

<section class="property-list">
@forelse($properties as $property)
    <article class="property-card">
        <div class="property-image">
            @if($property->firstImage || $property->has_video)
                <button class="dashboard-image-button" type="button" data-dashboard-gallery data-gallery-url="{{ route('properties.images', $property) }}" data-property-code="{{ $property->code }}" aria-label="{{ $property->has_video ? 'Xem video' : 'Xem ảnh' }} căn {{ $property->code }}">
                    @if($property->firstImage)
                        <img loading="lazy" src="{{ route('media.show', $property->firstImage) }}" alt="{{ $property->code }}">
                        <span class="image-zoom-hint">{{ $property->has_video ? '▶ Xem video' : '⌕ Xem ảnh' }}</span>
                    @else
                        <span>▶ Xem video</span>
                    @endif
                </button>
            @else<span>No image</span>@endif
        </div>
        <div class="cell info"><small>THÔNG TIN</small><strong>{{ $property->code }}</strong><b>{{ $property->project?->project_name ?? '—' }}</b><b>THÁP {{ $property->tower ?? '—' }}</b><b>TẦNG {{ $property->floor ?? '—' }}</b><b>SỐ {{ $property->room ?? '—' }}</b></div>
        <div class="cell type"><small>LOẠI / DIỆN TÍCH</small><b>LOẠI: {{ $property->property_type ?: '—' }}</b><b>DIỆN TÍCH: {{ $property->area ? rtrim(rtrim(number_format((float)$property->area, 2, ',', '.'), '0'), ',') : '—' }} m²</b><b>Nội thất: {{ $property->interior ?: 'Chưa rõ' }}</b></div>
        <div class="cell status"><small>TRẠNG THÁI</small><span class="status-pill {{ str_contains(mb_strtolower($property->status_new ?? ''), 'đang') ? 'rented' : '' }}">{{ $property->status_new ?: 'Chưa cập nhật' }}</span><b>Hạn thuê: {{ $property->rent_expiry?->format('d/m/Y') ?? '—' }}</b></div>
        <div class="cell prices"><small>GIÁ</small><b class="sell">Bán: {{ $property->price_sell ? number_format((float)$property->price_sell, 0, ',', '.') : '—' }}</b><b class="rent">Thuê: {{ $property->price_rent ? number_format((float)$property->price_rent, 0, ',', '.') : '—' }}</b><b class="commission">Hoa hồng: {{ $property->sales_commission ? number_format((float)$property->sales_commission, 0, ',', '.') : '—' }}</b></div>
        <div class="cell notes">
            <small>GHI CHÚ</small>
            @foreach([['1', 'Bán:', 'latestSaleNote', 'Ghi chú bán'], ['2', 'Thuê:', 'latestRentNote', 'Ghi chú thuê']] as [$group, $label, $relation, $title])
                <div class="note-row">
                    <b>{{ $label }}</b>
                    <span>{{ Str::limit($property->$relation?->note ?? '—', 58) }}</span>
                    <div class="note-actions">
                        <button
                            type="button"
                            class="note-action view"
                            data-view-notes
                            data-notes-url="{{ route('properties.notes.index', [$property, $group]) }}"
                            data-note-title="{{ $title }}"
                            data-property-code="{{ $property->code }}"
                        >Xem</button>
                        @if(auth()->user()->canEditProperties())
                            <button
                                type="button"
                                class="note-action add"
                                data-add-note
                                data-note-action="{{ route('properties.notes.store', $property) }}"
                                data-note-group="{{ $group }}"
                                data-note-title="Thêm {{ mb_strtolower($title) }}"
                                data-property-code="{{ $property->code }}"
                            >+ Thêm</button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="cell customers">
            <div class="customer-cell-head"><small>KHÁCH HÀNG</small><button type="button" class="customer-add-button" data-add-customer data-customer-action="{{ route('properties.customers.store', $property) }}" data-property-code="{{ $property->code }}">+ Thêm</button></div>
            @forelse($property->customers->take(2) as $customer)
                <div class="dashboard-customer">
                    <div><b>{{ $customer->full_name }}</b><span class="phone">☎ {{ $customer->phone1 ?: '—' }}</span></div>
                    @if(auth()->user()->isAdmin())
                        <form method="post" action="{{ route('properties.customers.destroy', [$property, $customer]) }}" onsubmit="return confirm('Xóa khách hàng này khỏi căn {{ $property->code }}?')">@csrf @method('DELETE')<button class="customer-delete-button" type="submit" title="Xóa khách hàng" aria-label="Xóa {{ $customer->full_name }}">×</button></form>
                    @endif
                </div>
            @empty<span>—</span>@endforelse
            @if($property->customers->count() > 2)<small class="customer-more">+{{ $property->customers->count() - 2 }} khách khác</small>@endif
        </div>
        <div class="cell other"><small>KHÁC</small><span>Cập nhật: {{ $property->updated_date?->format('d/m/Y') ?? '—' }}</span><a class="btn outline-green" href="{{ route('properties.show', $property) }}">Chi tiết</a>@if(auth()->user()->canEditProperties())<a class="btn outline-green" href="{{ route('properties.edit', $property) }}">Chỉnh sửa</a>@endif</div>
    </article>
@empty
    <div class="empty-state"><h2>Không tìm thấy căn phù hợp</h2><a class="btn primary" href="{{ route('dashboard') }}">Đặt lại bộ lọc</a></div>
@endforelse
</section>

@if($properties->hasPages())
    {{ $properties->onEachSide(1)->links('vendor.pagination.crm') }}
@endif

<div class="app-modal" data-notes-view-modal aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="notes-view-title">
    <section class="app-modal-dialog notes-view-dialog">
        <header class="app-modal-head">
            <div><h2 id="notes-view-title" data-notes-view-title>Ghi chú</h2><small data-notes-view-code></small></div>
            <button type="button" class="app-modal-close" data-close-notes-view aria-label="Đóng">×</button>
        </header>
        <div class="notes-modal-body" data-notes-view-body><p class="muted">Đang tải ghi chú...</p></div>
    </section>
</div>

@if(auth()->user()->canEditProperties())
<div class="app-modal" data-note-add-modal aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="note-add-title">
    <section class="app-modal-dialog note-add-dialog">
        <header class="app-modal-head">
            <div><h2 id="note-add-title" data-note-add-title>Thêm ghi chú</h2><small data-note-add-code></small></div>
            <button type="button" class="app-modal-close" data-close-note-add aria-label="Đóng">×</button>
        </header>
        <form method="post" action="" data-note-add-form>
            @csrf
            <input type="hidden" name="note_group" value="1" data-note-group-input>
            <div class="note-add-body">
                <fieldset class="quick-note-options">
                    <legend>Chọn nhanh</legend>
                    @foreach(['Không nghe máy', 'Thuê bao', 'Bận', 'Cúp máy ngang', 'Số sai', 'Số không tồn tại', 'Không nhu cầu thuê', 'Không nhu cầu bán', 'Không nhu cầu thuê/bán', 'Không nhu cầu mua mới', 'Gặp khách hàng đã hẹn', 'Khác'] as $reason)
                        <label><input type="radio" name="quick_reason" value="{{ $reason }}" data-note-reason> {{ $reason }}</label>
                    @endforeach
                </fieldset>
                <label class="note-content-label">Ghi chú
                    <textarea name="note" required maxlength="10000" rows="5" placeholder="Nhập nội dung ghi chú..." data-note-textarea></textarea>
                </label>
            </div>
            <footer class="app-modal-actions">
                <button class="btn ghost" type="button" data-close-note-add>Đóng</button>
                <button class="btn primary" type="submit">Lưu lại</button>
            </footer>
        </form>
    </section>
</div>
@endif

<div class="gallery-modal dashboard-gallery-modal" data-gallery-modal data-dynamic-gallery aria-hidden="true" role="dialog" aria-modal="true" aria-label="Ảnh và video căn hộ">
    <div class="dashboard-gallery-shell">
        <header class="dashboard-gallery-head">
            <div><strong>Ảnh và video</strong><small data-gallery-property></small></div>
            <div class="dashboard-gallery-tools">
                <a class="btn ghost gallery-download" data-gallery-download href="#" download>⇩ Tải ảnh</a>
                @if(auth()->user()->isAdmin())
                    <form method="post" action="" data-gallery-delete-form onsubmit="return confirm('Xóa tệp đang xem?')">@csrf @method('DELETE')<button class="btn media-modal-delete" type="submit">Xóa ảnh</button></form>
                @endif
                <button class="gallery-head-close" type="button" data-gallery-close aria-label="Đóng">×</button>
            </div>
        </header>
        <div class="gallery-dialog">
            <button class="gallery-nav gallery-prev" type="button" data-gallery-prev aria-label="Mục trước">‹</button>
            <figure class="gallery-stage">
                <img data-gallery-image alt="Hình ảnh căn hộ">
                <video data-gallery-video controls playsinline preload="metadata" aria-label="Video căn hộ" hidden></video>
                <p class="gallery-video-error" data-gallery-video-error role="status" hidden>Không thể phát video này trong trình duyệt.</p>
                <figcaption data-gallery-counter></figcaption>
            </figure>
            <button class="gallery-nav gallery-next" type="button" data-gallery-next aria-label="Mục tiếp theo">›</button>
        </div>
    </div>
</div>

<div class="app-modal customer-modal" data-customer-modal aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="customer-modal-title">
    <section class="app-modal-dialog customer-modal-dialog">
        <header class="app-modal-head"><div><h2 id="customer-modal-title">Thêm khách hàng</h2><small data-customer-property-code></small></div><button type="button" class="app-modal-close" data-close-customer-modal aria-label="Đóng">×</button></header>
        <form method="post" action="" data-customer-form>
            @csrf
            <div class="customer-modal-body">
                <label>Họ tên <span class="required">*</span><input name="full_name" required maxlength="255" autocomplete="name" placeholder="Nhập họ tên khách hàng"></label>
                <label>Số điện thoại <span class="required">*</span><input name="phone1" required maxlength="30" inputmode="tel" autocomplete="tel" placeholder="Ví dụ: 0912345678"></label>
            </div>
            <footer class="app-modal-actions"><button class="btn ghost" type="button" data-close-customer-modal>Đóng</button><button class="btn primary" type="submit">Lưu khách hàng</button></footer>
        </form>
    </section>
</div>

<div class="drawer-backdrop" data-filter-backdrop></div>
<aside class="filter-drawer" data-filter-drawer>
    <div class="drawer-head"><h2>Bộ lọc</h2><button type="button" data-close-filter>×</button></div>
    <form method="get" action="{{ route('dashboard') }}" class="filter-form">
        <label>Tìm kiếm<input name="q" value="{{ request('q') }}" placeholder="Mã căn, dự án, khách hàng..."></label>
        <div class="form-grid">
            <label>Dự án<select name="project_id"><option value="">Tất cả</option>@foreach($projects as $project)<option value="{{ $project->id }}" @selected(request('project_id') == $project->id)>{{ $project->project_name }}</option>@endforeach</select></label>
            @foreach(['tower'=>'Tháp','floor'=>'Tầng','room'=>'Số','property_type'=>'Loại căn','interior'=>'Nội thất','status_new'=>'Trạng thái'] as $key=>$label)
            <label>{{ $label }}<select name="{{ $key }}"><option value="">Tất cả</option>@foreach($options[$key] as $value)<option value="{{ $value }}" @selected(request($key)==$value)>{{ $value }}</option>@endforeach</select></label>
            @endforeach
        </div>
        @foreach([['price_sell','Giá bán'],['price_rent','Giá thuê'],['area','Diện tích (m²)'],['commission','Hoa hồng']] as [$key,$label])
        <fieldset><legend>{{ $label }}</legend><div class="range"><input type="number" name="{{ $key }}_min" value="{{ request($key.'_min') }}" placeholder="Từ"><input type="number" name="{{ $key }}_max" value="{{ request($key.'_max') }}" placeholder="Đến"></div></fieldset>
        @endforeach
        <label class="check"><input type="checkbox" name="has_image" value="1" @checked(request('has_image'))> Có hình ảnh</label>
        <label class="check"><input type="checkbox" name="has_document" value="1" @checked(request('has_document'))> Có hồ sơ / tài liệu</label>
        <label>Số kết quả / trang<select name="per_page">@foreach([10,20,50,100] as $size)<option value="{{ $size }}" @selected((int)request('per_page',20)===$size)>{{ $size }}</option>@endforeach</select></label>
        <div class="drawer-actions"><a class="btn ghost" href="{{ route('dashboard') }}">Reset</a><button class="btn primary" type="submit">Lọc</button></div>
    </form>
</aside>
@endsection
