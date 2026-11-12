<form method="post" action="{{ route('properties.images.store', $property) }}" enctype="multipart/form-data" class="image-upload-form">
    @csrf
    <label class="image-upload-picker">
        <span>Chọn hình ảnh hoặc video</span>
        <input type="file" name="media[]" accept="image/jpeg,image/png,image/webp,video/mp4,video/webm,video/quicktime,video/x-msvideo,video/x-matroska" multiple required data-image-upload-input>
        <small>Ảnh: JPG, PNG, WebP · Video: MP4, WebM, MOV, AVI, MKV · tối đa 20 tệp/lần · 100 MB/tệp</small>
    </label>
    <div class="image-upload-actions"><span class="muted" data-image-upload-count>Chưa chọn tệp</span><button class="btn primary" type="submit">↑ Tải lên</button></div>
</form>
