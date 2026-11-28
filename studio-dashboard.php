<?php
if (empty($auth)) return;
$modules = ['overview'=>['Tổng quan','chart-pie'],'content'=>['Thông tin đám cưới','rings-wedding'],'media'=>['Thư viện ảnh','picture'],'invitations'=>['Thiệp mời & QR','envelope-open'],'guests'=>['Khách mời & lời chúc','users'],'account'=>['Tài khoản','settings']];
$percent = $responseCount ? round(100 * ($responseCount - $stats['declines']) / $responseCount) : 0;
?>
<aside class="studio-side"><strong>Our Wedding</strong><small>ADMIN STUDIO</small><nav aria-label="Quản trị"><?php foreach ($modules as $id=>$item): ?><a href="#<?=$id?>" data-admin-section="<?=$id?>"><?=icon($item[1])?><span><?=e($item[0])?></span></a><?php endforeach ?></nav></aside>
<section id="overview">
<div class="studio-overview-heading"><div><p class="studio-section-kicker">WEDDING STUDIO <span aria-hidden="true">·</span> BẢNG ĐIỀU KHIỂN</p><h2><?=icon('chart-pie')?> Tổng quan</h2><p class="studio-section-intro">Tình hình khách mời, lời chúc và nội dung website cưới của bạn.</p></div><span class="studio-live-badge"><?=icon('heart')?> Ngày vui đang đến gần</span></div>
<div class="studio-metrics"><?php foreach (['Người tham dự'=>[$stats['guests'],'users','Khách đã xác nhận'],'Lời chúc'=>[$wishCount,'comment-heart','Tổng lời nhắn'],'Ảnh album'=>[count($d['gallery']),'picture','Khoảnh khắc đã tải'],'Sự kiện'=>[count($d['events']),'calendar-heart','Lịch ngày vui']] as $label=>$item): ?><article><span class="metric-icon"><?=icon($item[1])?></span><div><span class="metric-label"><?=e($label)?></span><b><?=$item[0]?></b><small><?=e($item[2])?></small></div><i class="metric-sparkle fi fi-rr-sparkles" aria-hidden="true"></i></article><?php endforeach ?></div>
<div class="studio-grid"><article class="studio-rsvp"><h2><?=icon('chart-pie')?> Tình trạng xác nhận tham dự</h2><div class="studio-chart"><strong style="--percent:<?=$percent?>%"><span><?=$percent?>%</span></strong><div><p><i class="status-dot status-yes" aria-hidden="true"></i>Tham dự <b><?=$responseCount-$stats['declines']?></b></p><p><i class="status-dot status-no" aria-hidden="true"></i>Từ chối <b><?=(int)$stats['declines']?></b></p><small>Trên tổng <?=$responseCount?> lượt phản hồi</small></div></div></article>
<article class="studio-couple"><span class="couple-icon"><?=icon('rings-wedding')?></span><small>THE WEDDING OF</small><h2><?=e($d['title'])?></h2><p class="couple-date"><?=icon('calendar-heart')?> <?=e(date('d.m.Y',strtotime($d['date'])))?></p><p class="couple-countdown"><?=icon('clock')?> Còn <?=max(0,(int)ceil((strtotime($d['date'])-time())/86400))?> ngày</p></article>
<article class="studio-recent"><h2><?=icon('comments')?> Khách phản hồi gần đây</h2><?php if (!$responses): ?><div class="studio-empty"><?=icon('envelope-open')?><p>Chưa có phản hồi</p><small>Khi khách gửi RSVP, thông tin sẽ xuất hiện tại đây.</small></div><?php else: ?><div class="table-wrap"><table><tr><th>Khách mời</th><th>Số người</th><th>Trạng thái</th></tr><?php foreach(array_slice($responses,0,5) as $r): ?><tr><td><?=e($r['name'])?></td><td><?=$r['attendance']==='yes'?(int)$r['guests']:0?></td><td><span class="response-badge <?=$r['attendance']==='yes'?'is-attending':'is-declining'?>"><?=icon($r['attendance']==='yes'?'check':'cross-small')?> <?=$r['attendance']==='yes'?'Tham dự':'Từ chối'?></span></td></tr><?php endforeach ?></table></div><?php endif ?></article>
<article class="studio-actions"><h2><?=icon('bolt')?> Thao tác nhanh</h2><a href="#content"><?=icon('pen-field')?> <span>Chỉnh sửa thông tin</span><?=icon('angle-right')?></a><a href="#media"><?=icon('camera')?> <span>Thêm ảnh cưới</span><?=icon('angle-right')?></a><a href="#invitations"><?=icon('envelope-open')?> <span>Tạo thiệp mời</span><?=icon('angle-right')?></a><a href="#guests"><?=icon('user-check')?> <span>Quản lý phản hồi</span><?=icon('angle-right')?></a></article></div></section>
<script>
function openStudioSection() {
  const sections = document.querySelectorAll('main.admin > section[id]');
  const current = Array.from(sections).find(section => '#' + section.id === location.hash) || document.getElementById('overview');
  sections.forEach(section => section.hidden = section !== current);
  document.querySelectorAll('.studio-side a').forEach(link => link.setAttribute('aria-current', link.hash === '#' + current.id ? 'page' : 'false'));
}
document.addEventListener('DOMContentLoaded', openStudioSection);
window.addEventListener('hashchange', openStudioSection);
</script>
