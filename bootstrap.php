<?php
declare(strict_types=1);
date_default_timezone_set('Asia/Ho_Chi_Minh');
ini_set('session.use_strict_mode','1');
session_set_cookie_params(['httponly'=>true,'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off','samesite'=>'Lax']);
session_start();header_remove('X-Powered-By');
header('X-Content-Type-Options: nosniff'); header('Referrer-Policy: strict-origin-when-cross-origin'); header('X-Frame-Options: SAMEORIGIN');
header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
function e($v): string {return htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function csrf(): string {return $_SESSION['csrf']??=bin2hex(random_bytes(32));}
function check_csrf(): void {if(!hash_equals(csrf(),(string)($_POST['csrf']??''))){http_response_code(403);exit('Phiên không hợp lệ.');}}
function db(): ?PDO {static $pdo=null; if($pdo)return $pdo; if(!is_file(__DIR__.'/config.php'))return null; $c=require __DIR__.'/config.php'; try{return $pdo=new PDO($c['dsn'],$c['user'],$c['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);}catch(Throwable $x){error_log($x->getMessage());return null;}}
function content(): array {$d=require __DIR__.'/defaults.php'; if($p=db()){try{$r=$p->query('SELECT content FROM settings WHERE id=1')->fetchColumn();if($r)$d=array_replace($d,json_decode($r,true,512,JSON_THROW_ON_ERROR));}catch(Throwable $x){error_log($x->getMessage());}}$defaults=require __DIR__.'/defaults.php';$d['gifts']=array_replace_recursive($defaults['gifts'],$d['gifts']??[]);return $d;}
function ready(): bool {try{return db()!==null && (int)db()->query('SELECT COUNT(*) FROM admins')->fetchColumn()>0;}catch(Throwable $x){return false;}}
function ensure_invitation_schema(): void {
 static $done=false;if($done)return;$p=db();if(!$p)throw new RuntimeException('Chưa kết nối MySQL.');
 $p->exec('CREATE TABLE IF NOT EXISTS invited_guests (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150) NOT NULL, token CHAR(64) NOT NULL UNIQUE, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, INDEX idx_invited_guests_created (created_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
 if(!$p->query("SHOW COLUMNS FROM responses LIKE 'guest_id'")->fetch())$p->exec('ALTER TABLE responses ADD COLUMN guest_id BIGINT UNSIGNED NULL, ADD INDEX idx_responses_guest (guest_id)');
 $done=true;
}
function site_base_url(): string {
 $c=is_file(__DIR__.'/config.php')?require __DIR__.'/config.php':[];$configured=trim((string)($c['site_url']??''));
 if($configured!==''&&filter_var($configured,FILTER_VALIDATE_URL)&&strtolower((string)parse_url($configured,PHP_URL_SCHEME))==='https')return rtrim($configured,'/');
 $host=(string)($_SERVER['HTTP_HOST']??'localhost');if(!preg_match('/^[a-z0-9.-]+(?::[0-9]{1,5})?$/iD',$host))$host='localhost';
 if(in_array(strtolower(preg_replace('/:\d+$/','',$host)),['localhost','127.0.0.1'],true))return 'https://wedding.quanuttuyen.com';
 $scheme=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')?'https':'http';$path=str_replace('\\','/',dirname((string)($_SERVER['SCRIPT_NAME']??'/')));$path=in_array($path,['.','/',''],true)?'':rtrim($path,'/');
 return $scheme.'://'.$host.$path;
}
function media_url($s): string {$s=(string)$s; return preg_match('~^(assets/|uploads/)[a-zA-Z0-9_./-]+$~D',$s)&&!str_contains($s,'..') || preg_match('~^https://[^\s]+$~D',$s)?$s:'';}
function redirect($s): never {header('Location: '.$s, true,303);exit;}
function icon($s): string {return '<i aria-hidden="true" class="fi fi-rr-'.e($s).'"></i>';}
function datevn($s): string {return $s?date('d/m/Y',strtotime($s)):'Chờ xác nhận';}
function text_length(string $value): int {return function_exists('mb_strlen')?mb_strlen($value,'UTF-8'):strlen($value);}
function client_fingerprint(): string {
 $ip=(string)($_SERVER['REMOTE_ADDR']??'unknown');
 return hash('sha256',$ip.'|'.(string)($_SERVER['HTTP_USER_AGENT']??''));
}
function rate_limit_submission(string $bucket, int $window=20): void {
 $now=time();$key=hash('sha256',$bucket.'|'.client_fingerprint());
 if($now-(int)($_SESSION['submit_'.$bucket]??0)<$window)throw new RuntimeException('Bạn thao tác quá nhanh. Vui lòng thử lại sau ít giây.');
 try{
  $p=db();if(!$p)throw new RuntimeException('no-db');
  $p->exec('CREATE TABLE IF NOT EXISTS submission_limits (rate_key CHAR(64) PRIMARY KEY, last_submit INT UNSIGNED NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
  $p->beginTransaction();$q=$p->prepare('SELECT last_submit FROM submission_limits WHERE rate_key=? FOR UPDATE');$q->execute([$key]);$last=(int)($q->fetchColumn()?:0);
  if($now-$last<$window){$p->rollBack();throw new RuntimeException('Bạn thao tác quá nhanh. Vui lòng thử lại sau ít giây.');}
  $p->prepare('INSERT INTO submission_limits(rate_key,last_submit) VALUES (?,?) ON DUPLICATE KEY UPDATE last_submit=VALUES(last_submit)')->execute([$key,$now]);$p->commit();
 }catch(RuntimeException $x){if($x->getMessage()!=='no-db')throw $x;}catch(Throwable $x){if(isset($p)&&$p?->inTransaction())$p->rollBack();error_log($x->getMessage());}
 $_SESSION['submit_'.$bucket]=$now;
}
