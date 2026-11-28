<?php
if (empty($auth)) return;
$modules = ['overview'=>['Tổng quan','chart-pie'],'content'=>['Thông tin đám cưới','rings-wedding'],'media'=>['Thư viện ảnh','picture'],'invitations'=>['Thiệp mời & QR','envelope'],'guests'=>['Khách mời & lời chúc','users'],'account'=>['Tài khoản','settings']];
$percent = $responseCount ? round(100 * ($responseCount - $stats['declines']) / $responseCount) : 0;
?>
<aside class="studio-side"><strong>Our Wedding</strong><small>ADMIN STUDIO</small><nav aria-label="Quản trị"><?php foreach ($modules as $id=>$item): ?><a href="#<?=$id?>" data-admin-section="<?=$id?>"><?=icon($item[1])?><span><?=e($item[0])?></span></a><?php endforeach ?></nav></aside>
<section id="overview">
<div class="studio-metrics"><?php foreach (['Người tham dự'=>[$stats['guests'],'users'],'Lời chúc'=>[$wishCount,'comment-heart'],'Ảnh album'=>[count($d['gallery']),'picture'],'Sự kiện'=>[count($d['events']),'calendar-heart']] as $label=>$item): ?><article><span class="metric-label"><?=icon($item[1])?><?=e($label)?></span><b><?=$item[0]?></b></article><?php endforeach ?></div>
<div class="studio-grid"><article><h2>Tình trạng xác nhận tham dự</h2><div class="studio-chart"><strong style="--percent:<?=$percent?>%"><span><?=$percent?>%</span></strong><div>Tham dự · <?=$responseCount-$stats['declines']?><br>Từ chối · <?=(int)$stats['declines']?></div></div></article>
<article class="studio-couple"><small>THE WEDDING OF</small><h2><?=e($d['title'])?></h2><?=e(date('d.m.Y',strtotime($d['date'])))?><p>Còn <?=max(0,(int)ceil((strtotime($d['date'])-time())/86400))?> ngày</p></article>
<article><h2>Khách phản hồi gần đây</h2><?php if (!$responses): ?><p>Chưa có phản hồi.</p><?php else: ?><table><tr><th>Khách mời</th><th>Số người</th><th>Trạng thái</th></tr><?php foreach(array_slice($responses,0,5) as $r): ?><tr><td><?=e($r['name'])?></td><td><?=$r['attendance']==='yes'?(int)$r['guests']:0?></td><td><?=$r['attendance']==='yes'?'Tham dự':'Từ chối'?></td></tr><?php endforeach ?></table><?php endif ?></article>
<article class="studio-actions"><h2>Thao tác nhanh</h2><a href="#content">Chỉnh sửa thông tin</a><a href="#media">Thêm ảnh cưới</a><a href="#invitations">Tạo thiệp mời</a><a href="#guests">Quản lý phản hồi</a></article></div></section>
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
