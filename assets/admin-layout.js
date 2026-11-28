// Group existing fields without cloning inputs or changing form submission.
document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('#content > form');
  if (!form) return;
  const nodes = Array.from(form.children);
  const grid = document.createElement('div');
  grid.className = 'studio-editor-grid';
  const groups = {};
  const createGroup = (key, title, icon) => {
    const card = document.createElement('div');
    card.className = `studio-editor-card studio-editor-${key}`;
    const heading = document.createElement('h3');
    const symbol = document.createElement('i');
    symbol.className = `fi fi-rr-${icon}`;
    symbol.setAttribute('aria-hidden', 'true');
    heading.append(symbol, document.createTextNode(title));
    card.append(heading);
    grid.append(card);
    groups[key] = card;
  };
  createGroup('info', 'Thông tin đám cưới', 'pen-field');
  createGroup('events', 'Từng sự kiện cưới', 'calendar-heart');
  createGroup('gifts', 'Mừng cưới & mã QR', 'gift');
  createGroup('media', 'Hình ảnh & nhạc nền', 'picture');
  let current = 'info';
  nodes.forEach(node => {
    if (node.matches('input[type="hidden"]')) return;
    if (node.tagName === 'H2') {
      current = node.querySelector('.fi-rr-bank') ? 'gifts' : 'media';
      node.hidden = true;
    }
    if (node.matches('fieldset') && node.querySelector('input[name^="events["]')) current = 'events';
    if (node.matches('button[type="submit"], button:not([type])')) {
      const bar = document.createElement('div');
      bar.className = 'studio-save-bar';
      const note = document.createElement('span');
      note.textContent = 'Lưu thay đổi để cập nhật thiệp cưới của bạn';
      bar.append(note, node);
      form.append(bar);
      return;
    }
    groups[current].append(node);
  });
  form.prepend(grid);
});
