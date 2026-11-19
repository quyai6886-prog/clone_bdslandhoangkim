document.addEventListener('DOMContentLoaded', () => {
    const mobileNavToggle = document.querySelector('[data-mobile-nav-toggle]');
    const mobileNav = document.querySelector('[data-mobile-nav]');

    const closeMobileNav = () => {
        mobileNav?.classList.remove('open');
        mobileNavToggle?.setAttribute('aria-expanded', 'false');
        if (mobileNavToggle) mobileNavToggle.textContent = '☰';
    };
    mobileNavToggle?.addEventListener('click', event => {
        event.stopPropagation();
        const isOpen = mobileNav?.classList.toggle('open');
        mobileNavToggle.setAttribute('aria-expanded', String(Boolean(isOpen)));
        mobileNavToggle.textContent = isOpen ? '×' : '☰';
    });
    document.addEventListener('click', event => {
        if (mobileNav?.classList.contains('open') && !mobileNav.contains(event.target) && event.target !== mobileNavToggle) closeMobileNav();
    });
    window.addEventListener('resize', () => {
        if (window.innerWidth > 820) closeMobileNav();
    });
    const drawer = document.querySelector('[data-filter-drawer]');
    const backdrop = document.querySelector('[data-filter-backdrop]');
    const gallery = document.querySelector('[data-gallery-modal]');
    const galleryItems = [...document.querySelectorAll('[data-gallery-item]')];
    const galleryImage = gallery?.querySelector('[data-gallery-image]');
    const galleryVideo = gallery?.querySelector('[data-gallery-video]');
    const galleryVideoError = gallery?.querySelector('[data-gallery-video-error]');
    const galleryCounter = gallery?.querySelector('[data-gallery-counter]');
    const galleryProperty = gallery?.querySelector('[data-gallery-property]');
    const galleryDownload = gallery?.querySelector('[data-gallery-download]');
    const dashboardGalleryButtons = [...document.querySelectorAll('[data-dashboard-gallery]')];
    const galleryDeleteForm = gallery?.querySelector('[data-gallery-delete-form]');
    const notesViewModal = document.querySelector('[data-notes-view-modal]');
    const noteAddModal = document.querySelector('[data-note-add-modal]');
    const noteAddForm = noteAddModal?.querySelector('[data-note-add-form]');
    const noteTextarea = noteAddModal?.querySelector('[data-note-textarea]');
    const customerModal = document.querySelector('[data-customer-modal]');
    const customerForm = customerModal?.querySelector('[data-customer-form]');
    const customerPropertyCode = customerModal?.querySelector('[data-customer-property-code]');
    let currentMedia = 0;
    let previousQuickReason = '';

    const syncBodyLock = () => {
        const overlayOpen = drawer?.classList.contains('open') || gallery?.classList.contains('open')
            || notesViewModal?.classList.contains('open') || noteAddModal?.classList.contains('open')
            || customerModal?.classList.contains('open');
        document.body.classList.toggle('no-scroll', Boolean(overlayOpen));
    };

    const openDrawer = () => {
        drawer?.classList.add('open');
        backdrop?.classList.add('open');
        syncBodyLock();
    };
    const closeDrawer = () => {
        drawer?.classList.remove('open');
        backdrop?.classList.remove('open');
        syncBodyLock();
    };

    const clearGalleryVideo = () => {
        if (!galleryVideo?.hasAttribute('src')) return;
        galleryVideo.pause();
        galleryVideo.removeAttribute('src');
        galleryVideo.load();
    };
    const renderMedia = index => {
        if (!galleryItems.length || !galleryImage) return;
        currentMedia = (index + galleryItems.length) % galleryItems.length;
        const item = galleryItems[currentMedia];
        const source = item.dataset?.gallerySrc || item.src;
        const isVideo = Boolean(galleryVideo && item.isVideo);
        const mediaLabel = isVideo ? 'video' : 'ảnh';
        galleryImage.hidden = isVideo;
        if (!isVideo) galleryImage.src = source;
        if (galleryVideo) {
            galleryVideo.hidden = !isVideo;
            galleryVideoError.hidden = true;
            if (isVideo) {
                galleryVideo.src = source;
            } else {
                clearGalleryVideo();
            }
        }
        if (galleryDownload) {
            galleryDownload.href = source;
            galleryDownload.textContent = `⇩ Tải ${mediaLabel}`;
        }
        if (galleryDeleteForm) {
            galleryDeleteForm.action = item.delete_url || '';
            galleryDeleteForm.hidden = !item.delete_url;
            galleryDeleteForm.querySelector('button').textContent = `Xóa ${mediaLabel}`;
        }
        if (galleryCounter) galleryCounter.textContent = `${currentMedia + 1} / ${galleryItems.length}`;
    };
    const openGallery = index => {
        if (!gallery) return;
        renderMedia(index);
        gallery.classList.add('open');
        gallery.setAttribute('aria-hidden', 'false');
        syncBodyLock();
        gallery.querySelector('[data-gallery-close]')?.focus();
    };
    const closeGallery = () => {
        if (!gallery) return;
        gallery.classList.remove('open');
        gallery.setAttribute('aria-hidden', 'true');
        galleryImage?.removeAttribute('src');
        clearGalleryVideo();
        syncBodyLock();
    };

    const openModal = modal => {
        modal?.classList.add('open');
        modal?.setAttribute('aria-hidden', 'false');
        syncBodyLock();
        modal?.querySelector('.app-modal-close')?.focus();
    };
    const closeModal = modal => {
        modal?.classList.remove('open');
        modal?.setAttribute('aria-hidden', 'true');
        syncBodyLock();
    };

    const makeNoteCard = note => {
        const article = document.createElement('article');
        article.className = 'notes-modal-card';
        const meta = document.createElement('div');
        meta.className = 'notes-modal-meta';
        const date = document.createElement('time');
        date.textContent = `◷ ${note.note_date || 'Không rõ thời gian'}`;
        const author = document.createElement('strong');
        author.textContent = note.author || 'Không rõ';
        const content = document.createElement('p');
        content.textContent = note.note || '—';
        meta.append(date, author);
        article.append(meta, content);
        return article;
    };

    document.querySelectorAll('[data-view-notes]').forEach(button => button.addEventListener('click', async () => {
        if (!notesViewModal) return;
        notesViewModal.querySelector('[data-notes-view-title]').textContent = button.dataset.noteTitle || 'Ghi chú';
        notesViewModal.querySelector('[data-notes-view-code]').textContent = button.dataset.propertyCode || '';
        const body = notesViewModal.querySelector('[data-notes-view-body]');
        body.replaceChildren();
        const loading = document.createElement('p');
        loading.className = 'muted';
        loading.textContent = 'Đang tải ghi chú...';
        body.append(loading);
        openModal(notesViewModal);
        try {
            const response = await fetch(button.dataset.notesUrl, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const payload = await response.json();
            body.replaceChildren();
            if (!payload.notes.length) {
                const empty = document.createElement('p');
                empty.className = 'muted notes-modal-empty';
                empty.textContent = 'Chưa có ghi chú.';
                body.append(empty);
                return;
            }
            payload.notes.forEach(note => body.append(makeNoteCard(note)));
        } catch (error) {
            body.replaceChildren();
            const message = document.createElement('p');
            message.className = 'inline-error';
            message.textContent = 'Không tải được ghi chú. Vui lòng thử lại.';
            body.append(message);
        }
    }));

    document.querySelectorAll('[data-add-note]').forEach(button => button.addEventListener('click', () => {
        if (!noteAddModal || !noteAddForm) return;
        noteAddForm.action = button.dataset.noteAction;
        noteAddModal.querySelector('[data-note-group-input]').value = button.dataset.noteGroup;
        noteAddModal.querySelector('[data-note-add-title]').textContent = button.dataset.noteTitle || 'Thêm ghi chú';
        noteAddModal.querySelector('[data-note-add-code]').textContent = button.dataset.propertyCode || '';
        noteAddForm.reset();
        noteAddModal.querySelector('[data-note-group-input]').value = button.dataset.noteGroup;
        previousQuickReason = '';
        openModal(noteAddModal);
    }));

    noteAddModal?.querySelectorAll('[data-note-reason]').forEach(radio => radio.addEventListener('change', () => {
        if (!noteTextarea) return;
        const current = noteTextarea.value.trim();
        if (!current || current === previousQuickReason) noteTextarea.value = radio.value;
        previousQuickReason = radio.value;
        noteTextarea.focus();
    }));

    document.querySelectorAll('[data-open-filter]').forEach(button => button.addEventListener('click', openDrawer));
    document.querySelectorAll('[data-add-customer]').forEach(button => button.addEventListener('click', () => {
        if (!customerModal || !customerForm) return;
        customerForm.action = button.dataset.customerAction;
        customerForm.reset();
        if (customerPropertyCode) customerPropertyCode.textContent = button.dataset.propertyCode || '';
        openModal(customerModal);
    }));
    document.querySelectorAll('[data-close-customer-modal]').forEach(button => button.addEventListener('click', () => closeModal(customerModal)));
    document.querySelectorAll('[data-close-filter]').forEach(button => button.addEventListener('click', closeDrawer));
    backdrop?.addEventListener('click', closeDrawer);
    galleryItems.forEach((item, index) => item.addEventListener('click', () => openGallery(index)));
    dashboardGalleryButtons.forEach(button => button.addEventListener('click', async () => {
        if (!gallery) return;
        button.disabled = true;
        try {
            const response = await fetch(button.dataset.galleryUrl, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const payload = await response.json();
            const videos = (payload.videos || []).map(video => ({ ...video, isVideo: true }));
            galleryItems.splice(0, galleryItems.length, ...videos, ...(payload.images || []));
            if (!galleryItems.length) throw new Error('Không có ảnh hoặc video');
            if (galleryProperty) galleryProperty.textContent = payload.property || button.dataset.propertyCode || '';
            openGallery(0);
            if (videos.length && galleryVideo) {
                galleryVideo.muted = true;
                galleryVideo.play().catch(() => {});
            }
        } catch (error) {
            window.alert('Không thể tải ảnh hoặc video của căn này. Vui lòng thử lại.');
        } finally {
            button.disabled = false;
        }
    }));
    gallery?.querySelector('[data-gallery-close]')?.addEventListener('click', closeGallery);
    gallery?.querySelector('[data-gallery-prev]')?.addEventListener('click', () => renderMedia(currentMedia - 1));
    gallery?.querySelector('[data-gallery-next]')?.addEventListener('click', () => renderMedia(currentMedia + 1));
    galleryVideo?.addEventListener('error', () => {
        if (!galleryVideo.hasAttribute('src')) return;
        galleryVideo.hidden = true;
        galleryVideoError.hidden = false;
    });
    gallery?.addEventListener('click', event => {
        if (event.target === gallery) closeGallery();
    });
    document.querySelectorAll('[data-close-notes-view]').forEach(button => button.addEventListener('click', () => closeModal(notesViewModal)));
    document.querySelectorAll('[data-close-note-add]').forEach(button => button.addEventListener('click', () => closeModal(noteAddModal)));
    notesViewModal?.addEventListener('click', event => {
        if (event.target === notesViewModal) closeModal(notesViewModal);
    });
    noteAddModal?.addEventListener('click', event => {
        if (event.target === noteAddModal) closeModal(noteAddModal);
    customerModal?.addEventListener('click', event => {
        if (event.target === customerModal) closeModal(customerModal);
    });
    });

    document.addEventListener('keydown', event => {
        if (gallery?.classList.contains('open')) {
            if (event.key === 'Escape') closeGallery();
            if (event.target === galleryVideo) return;
            if (event.key === 'ArrowLeft') renderMedia(currentMedia - 1);
            if (event.key === 'ArrowRight') renderMedia(currentMedia + 1);
            return;
        }
        if (notesViewModal?.classList.contains('open')) {
            if (event.key === 'Escape') closeModal(notesViewModal);
            return;
        }
        if (noteAddModal?.classList.contains('open')) {
            if (event.key === 'Escape') closeModal(noteAddModal);
            return;
        if (customerModal?.classList.contains('open')) {
            if (event.key === 'Escape') closeModal(customerModal);
            return;
        }
        }
        if (event.key === 'Escape') closeDrawer();
    });

    document.querySelectorAll('[data-image-upload-input]').forEach(input => input.addEventListener('change', () => {
        const count = input.files?.length || 0;
        const label = input.closest('.image-upload-form')?.querySelector('[data-image-upload-count]');
        if (label) label.textContent = count ? `Đã chọn ${count} ảnh` : 'Chưa chọn ảnh';
    }));
});

