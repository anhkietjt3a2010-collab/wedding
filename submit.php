<?php
require __DIR__.'/bootstrap.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit;}
check_csrf();
if(!ready()){http_response_code(503);exit('Chưa cài đặt.');}
$type=$_POST['type']??'';$section=$type==='wish'?'wishes':'rsvp';
try{
 if(trim((string)($_POST['website']??''))!=='')throw new Exception('Yêu cầu không hợp lệ.');
 rate_limit_submission($type==='wish'?'wish':'rsvp',20);
 $name=trim((string)($_POST['name']??''));$guestId=null;
 if($type==='wish'){
  if($name===''||text_length($name)>150)throw new Exception('Tên không hợp lệ.');
  $message=trim((string)($_POST['message']??''));if($message===''||text_length($message)>1000)throw new Exception('Lời chúc không hợp lệ.');
  $q=db()->prepare('INSERT INTO wishes(name,message) VALUES (?,?)');$q->execute([$name,$message]);$_SESSION['flash']='Cảm ơn bạn! Lời chúc đang chờ duyệt.';
 }elseif($type==='rsvp'){
  ensure_invitation_schema();$token=trim((string)($_POST['guest_token']??''));
  if($token!==''){
   if(!preg_match('/^[a-f0-9]{64}$/D',$token))throw new Exception('Liên kết thiệp mời không hợp lệ.');
   $lookup=db()->prepare('SELECT id,name FROM invited_guests WHERE token=?');$lookup->execute([$token]);$invited=$lookup->fetch();if(!$invited)throw new Exception('Liên kết thiệp mời không còn hiệu lực.');
   $guestId=(int)$invited['id'];$name=(string)$invited['name'];
  }
  if($name===''||text_length($name)>150)throw new Exception('Tên không hợp lệ.');
  $a=$_POST['attendance']??'';$n=filter_var($_POST['guests']??0,FILTER_VALIDATE_INT);$event=(string)($_POST['event_name']??'');$note=trim((string)($_POST['note']??''));
  if(!in_array($a,['yes','no'],true)||($a==='yes'&&($n===false||$n<1||$n>10))||!in_array($event,array_column(content()['events'],'name'),true)||text_length($note)>1000)throw new Exception('Thông tin xác nhận không hợp lệ.');
  if($guestId){$p=db();$p->beginTransaction();try{$existing=$p->prepare('SELECT response_id FROM invitation_responses WHERE guest_id=? LIMIT 1');$existing->execute([$guestId]);$responseId=$existing->fetchColumn();if($responseId){$q=$p->prepare('UPDATE responses SET name=?,attendance=?,guests=?,event_name=?,note=? WHERE id=?');$q->execute([$name,$a,$a==='yes'?$n:0,$event,$note,$responseId]);}else{$q=$p->prepare('INSERT INTO responses(name,attendance,guests,event_name,note) VALUES (?,?,?,?,?)');$q->execute([$name,$a,$a==='yes'?$n:0,$event,$note]);$responseId=(int)$p->lastInsertId();$p->prepare('INSERT INTO invitation_responses(guest_id,response_id) VALUES (?,?)')->execute([$guestId,$responseId]);}$p->commit();}catch(Throwable $x){if($p->inTransaction())$p->rollBack();throw $x;}}
  else{$q=db()->prepare('INSERT INTO responses(name,attendance,guests,event_name,note) VALUES (?,?,?,?,?)');$q->execute([$name,$a,$a==='yes'?$n:0,$event,$note]);}
  $_SESSION['flash']='Cảm ơn bạn đã xác nhận!';
 }else throw new Exception('Yêu cầu không hợp lệ.');
}catch(Throwable $x){$_SESSION['flash']=$x instanceof PDOException?'Không lưu được dữ liệu.':$x->getMessage();}
redirect('index.php#'.$section);
