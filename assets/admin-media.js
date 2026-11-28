(() => {
  const csrf = document.querySelector('input[name="csrf"]')?.value;
  if (!csrf) return;
  const selector = 'input[name="groom_photo"], input[name="bride_photo"], input[name$="[photo]"], input[name^="gifts["][name$="[qr]"], textarea[name="slides"], textarea[name="gallery"]';
  const targets = document.querySelectorAll(selector);

  async function upload(files, target, status) {
    const chosen = [...files].filter(file => file?.type.startsWith('image/'));
    if (!chosen.length) {
      status.textContent = 'Hãy chọn hoặc dán tệp ảnh JPEG, PNG hoặc WebP.';
      return;
    }
    if (!(target instanceof HTMLTextAreaElement) && chosen.length > 1) {
      status.textContent = 'Ô này chỉ nhận một ảnh. Album và slider mới hỗ trợ chọn nhiều ảnh.';
      return;
    }
    status.textContent = `Đang tải ${chosen.length} ảnh lên…`;
    const form = new FormData();
    form.append('csrf', csrf);
    form.append('action', 'upload');
    form.append('ajax_upload', '1');
    chosen.forEach(file => form.append('photos[]', file, file.name || 'pasted-image.png'));
    try {
      const response = await fetch('admin.php', { method: 'POST', body: form, credentials: 'same-origin' });
      const result = await response.json();
      if (!response.ok || !result.ok) throw new Error(result.error || 'Tải ảnh không thành công.');
      const paths = result.paths || (result.path ? [result.path] : []);
      if (target instanceof HTMLTextAreaElement) {
        const lines = target.value.split(/\r?\n/).map(line => line.trim()).filter(Boolean);
        target.value = [...lines, ...paths.filter(path => !lines.includes(path))].join('\n');
        target.dispatchEvent(new Event('input', { bubbles: true }));
      } else {
        target.value = paths[0] || '';
      }
      target.dispatchEvent(new Event('change', { bubbles: true }));
      status.textContent = `Đã tải ${paths.length} ảnh. Nhấn “Lưu & cập nhật website” để áp dụng.`;
    } catch (error) {
      status.textContent = error.message || 'Không tải được ảnh. Vui lòng thử lại.';
    }
  }

  function renderList(target, manager) {
    const lines = target.value.split(/\r?\n/).map(line => line.trim()).filter(Boolean);
    manager.replaceChildren();
    lines.forEach((path, index) => {
      const item = document.createElement('div');
      item.className = 'media-list-item';
      item.draggable = true;
      item.dataset.index = String(index);
      const image = document.createElement('img');
      image.alt = '';
      image.loading = 'lazy';
      image.src = /^(?:assets\/|uploads\/|https:\/\/)/.test(path) ? path : '';
      const name = document.createElement('span');
      name.className = 'media-list-name';
      name.textContent = path;
      name.title = path;
      const order = document.createElement('small');
      order.className = 'media-list-order';
      order.textContent = `Ảnh ${index + 1} / ${lines.length} · kéo để đổi vị trí`;
      const moveUp = document.createElement('button');
      moveUp.type = 'button';
      moveUp.className = 'media-list-action';
      moveUp.textContent = '↑ Lên';
      moveUp.setAttribute('aria-label', `Chuyển ảnh ${index + 1} lên trước`);
      moveUp.disabled = index === 0;
      const moveDown = document.createElement('button');
      moveDown.type = 'button';
      moveDown.className = 'media-list-action';
      moveDown.textContent = '↓ Xuống';
      moveDown.setAttribute('aria-label', `Chuyển ảnh ${index + 1} xuống sau`);
      moveDown.disabled = index === lines.length - 1;
      const remove = document.createElement('button');
      remove.type = 'button';
      remove.className = 'media-list-remove';
      remove.textContent = 'Gỡ ảnh';
      remove.setAttribute('aria-label', `Gỡ ảnh ${index + 1} khỏi danh sách`);
      const changeOrder = destination => {
        const reordered = [...lines];
        [reordered[index], reordered[destination]] = [reordered[destination], reordered[index]];
        target.value = reordered.join('\n');
        target.dispatchEvent(new Event('input', { bubbles: true }));
      };
      moveUp.addEventListener('click', () => changeOrder(index - 1));
      moveDown.addEventListener('click', () => changeOrder(index + 1));
      remove.addEventListener('click', () => {
        if (!window.confirm('Gỡ ảnh này khỏi danh sách? Tệp vẫn còn trong thư viện ảnh.')) return;
        target.value = lines.filter((_, i) => i !== index).join('\n');
        target.dispatchEvent(new Event('input', { bubbles: true }));
      });
      const details = document.createElement('div');
      details.className = 'media-list-details';
      details.append(order, name);
      const actions = document.createElement('div');
      actions.className = 'media-list-actions';
      actions.append(moveUp, moveDown, remove);
      item.append(image, details, actions);
      item.addEventListener('dragstart', event => {
        item.classList.add('is-dragging');
        event.dataTransfer?.setData('text/plain', String(index));
        if (event.dataTransfer) event.dataTransfer.effectAllowed = 'move';
      });
      item.addEventListener('dragend', () => item.classList.remove('is-dragging'));
      item.addEventListener('dragover', event => { event.preventDefault(); item.classList.add('drop-target'); });
      item.addEventListener('dragleave', () => item.classList.remove('drop-target'));
      item.addEventListener('drop', event => {
        event.preventDefault();
        item.classList.remove('drop-target');
        const source = Number(event.dataTransfer?.getData('text/plain'));
        if (!Number.isInteger(source) || source === index || source < 0 || source >= lines.length) return;
        const reordered = [...lines];
        const [moved] = reordered.splice(source, 1);
        reordered.splice(index, 0, moved);
        target.value = reordered.join('\n');
        target.dispatchEvent(new Event('input', { bubbles: true }));
        statusMessage(target, `Đã chuyển ảnh ${source + 1} tới vị trí ${index + 1}.`);
      });
      manager.append(item);
    });
    manager.hidden = lines.length === 0;
  }

  targets.forEach(target => {
    target.dataset.pasteUpload = 'true';
    const host = target.closest('label') || target.parentElement;
    if (!host) return;
    const controls = document.createElement('div');
    controls.className = 'media-upload-controls';
    const choose = document.createElement('button');
    choose.type = 'button';
    choose.className = 'media-upload-button';
    choose.textContent = target instanceof HTMLTextAreaElement ? 'Chọn nhiều ảnh để tải lên' : 'Chọn ảnh để tải lên';
    const picker = document.createElement('input');
    picker.type = 'file';
    picker.accept = 'image/jpeg,image/png,image/webp';
    picker.multiple = target instanceof HTMLTextAreaElement;
    picker.hidden = true;
    const status = document.createElement('small');
    status.className = 'media-upload-status';
    status.setAttribute('aria-live', 'polite');
    status.textContent = target instanceof HTMLTextAreaElement
      ? 'Có thể chọn nhiều ảnh cùng lúc, kéo thả vào danh sách hoặc dán ảnh bằng Ctrl+V.'
      : 'Hoặc focus ô này rồi nhấn Ctrl+V để dán ảnh.';
    choose.addEventListener('click', () => picker.click());
    picker.addEventListener('change', () => {
      if (picker.files?.length) upload(picker.files, target, status);
      picker.value = '';
    });
    controls.append(choose, picker, status);
    host.append(controls);
    target.dataset.mediaStatus = 'true';

    if (target instanceof HTMLTextAreaElement) {
      const manager = document.createElement('div');
      manager.className = 'media-list-manager';
      manager.setAttribute('aria-label', 'Sắp xếp và gỡ ảnh');
      host.append(manager);
      renderList(target, manager);
      target.addEventListener('input', () => renderList(target, manager));
      const acceptDrop = event => {
        const files = [...(event.dataTransfer?.files || [])].filter(file => file.type.startsWith('image/'));
        if (!files.length) return;
        event.preventDefault();
        upload(files, target, status);
      };
      [target, manager].forEach(area => {
        area.addEventListener('dragover', event => {
          if ([...(event.dataTransfer?.items || [])].some(item => item.kind === 'file')) event.preventDefault();
        });
        area.addEventListener('drop', acceptDrop);
      });
    }
  });

  function statusMessage(target, message) {
    const host = target.closest('label') || target.parentElement;
    const status = host?.querySelector('.media-upload-status');
    if (status) status.textContent = message;
  }

  const sectionIcons = new Map([
    ['Chỉnh sửa nội dung website', 'edit'], ['Thông tin mừng cưới và QR', 'gift'],
    ['Media & nhạc nền', 'picture'], ['Thư viện ảnh đã tải', 'folder-open'],
    ['Tạo thiệp mời riêng', 'envelope'], ['Khách mời & lời chúc', 'users'],
    ['Khách xác nhận tham dự', 'calendar-check'], ['Duyệt lời chúc', 'comment-heart'],
    ['Đổi mật khẩu', 'lock'], ['Tình trạng xác nhận tham dự', 'chart-pie'],
    ['Khách phản hồi gần đây', 'users'], ['Thao tác nhanh', 'bolt']
  ]);
  document.querySelectorAll('main.admin h2').forEach(heading => {
    const iconName = sectionIcons.get(heading.textContent.trim());
    if (!iconName || heading.querySelector('i')) return;
    const icon = document.createElement('i');
    icon.className = `fi fi-rr-${iconName}`;
    icon.setAttribute('aria-hidden', 'true');
    heading.prepend(icon);
  });

  document.addEventListener('paste', event => {
    const target = event.target instanceof Element && event.target.matches('[data-paste-upload="true"]')
      ? event.target
      : document.activeElement;
    if (!(target instanceof HTMLElement) || target.dataset.pasteUpload !== 'true') return;
    const images = [...(event.clipboardData?.items || [])]
      .filter(item => item.kind === 'file' && item.type.startsWith('image/'))
      .map(item => item.getAsFile())
      .filter(Boolean);
    if (!images.length) return;
    event.preventDefault();
    const status = target.closest('label')?.querySelector('.media-upload-status');
    if (status) upload(images, target, status);
  });
})();
