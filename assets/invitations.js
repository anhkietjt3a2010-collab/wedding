(() => {
  document.querySelectorAll('[data-qr-value]').forEach(node => {
    if (typeof QRCode === 'undefined') {
      node.textContent = 'Không tải được bộ tạo QR.';
      return;
    }
    new QRCode(node, {
      text: node.dataset.qrValue,
      width: 176,
      height: 176,
      colorDark: '#603947',
      colorLight: '#ffffff',
      correctLevel: QRCode.CorrectLevel.M
    });
  });

  document.querySelectorAll('[data-copy-url]').forEach(button => {
    button.addEventListener('click', async () => {
      const url = button.dataset.copyUrl;
      try {
        if (navigator.clipboard && window.isSecureContext) {
          await navigator.clipboard.writeText(url);
        } else {
          const field = document.createElement('textarea');
          field.value = url;
          field.setAttribute('readonly', '');
          field.style.position = 'fixed';
          field.style.opacity = '0';
          document.body.append(field);
          field.select();
          const copied = document.execCommand('copy');
          field.remove();
          if (!copied) throw new Error('copy failed');
        }
        button.textContent = 'Đã sao chép link';
      } catch {
        button.textContent = 'Không chép được · hãy chọn link';
      }
      window.setTimeout(() => { button.textContent = 'Sao chép link'; }, 2400);
    });
  });
})();
