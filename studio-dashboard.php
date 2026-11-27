<?php
if (empty($auth)) return;
$modules = ['overview'=>'Tổng quan','content'=>'Thông tin đám cưới','media'=>'Thư viện ảnh','invitations'=>'Thiệp mời & QR','guests'=>'Khách mời & lời chúc','account'=>'Tài khoản'];
$percent = $responseCount ? round(100 * ($responseCount - $stats['declines']) / $responseCount) : 0;
?>
<aside class="studio-side"><strong>Our Wedding</strong><small>ADMIN STUDIO</small><nav aria-label="Quản trị"><?php foreach ($modules as $id=>$label): ?><a href="#<?=$id?>"><?=e($label)?></a><?php endforeach ?></nav></aside>
<section id="overview">
<div class="studio-metrics"><?php foreach (['Người tham dự'=>$stats['guests'],'Lời chúc'=>$wishCount,'Ảnh album'=>count($d['gallery']),'Sự kiện'=>count($d['events'])] as $label=>$value): ?><article><?=e($label)?><b><?=e($value)?></b></article><?php endforeach ?></div>
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
