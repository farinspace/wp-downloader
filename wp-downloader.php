<?php

// WordPress Downloader is released under the GPL Version 2, June 1991
// http://www.gnu.org/licenses/old-licenses/gpl-2.0.html

if ($_REQUEST)
{
	try
	{
		$url = empty($_REQUEST['url']) ? '' : $_REQUEST['url'] ;

		if (empty($url)) throw new Exception('A WordPress release link is required, it is recommended that you use the latest WordPress Zip link: http://wordpress.org/latest.zip');

		$keep_wordpress_folder = !empty($_REQUEST['kwf']) ? TRUE : FALSE ;

		$delete_self = !empty($_REQUEST['ds']) ? TRUE : FALSE ;

		$ua = 'WordPress Downloader (http://farinspace.com/2010/04/wordpress-downloader/)';

		$file = 'wordpress.zip';

		$fp = fopen($file,'w');

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, $ua);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, 0); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 120);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		$b = curl_exec($ch);

		if (!$b) 
		{
			$message = 'Download error: '. curl_error($ch) .', please try again';
			curl_close($ch);
			throw new Exception($message);
		}

		fclose($fp);

		if (!file_exists($file)) throw new Exception('Zip file not downloaded');

		if (class_exists('ZipArchive'))
		{
			$zip = new ZipArchive;
		
			if($zip->open($file) !== TRUE) throw new Exception('Unable to open Zip file');
		
			$zip->extractTo('./');
		
			$zip->close();
		}
		else
		{
			// try unix shell command
			@shell_exec('unzip -d ./ '. $file);
		}

		// remove Zip file
		unlink($file);

		// check if extraction worked
		if (!is_dir('./wordpress')) throw new Exception('Unable to open Zip file');
		
		$wordpress_folder = 'wordpress/';

		if (!$keep_wordpress_folder)
		{
			recursive_move('./wordpress','./');
			recursive_remove('./wordpress');

			$wordpress_folder = '';
		}

		if ($delete_self)
		{
			unlink('./wp-downloader.php');
		}

		$status = array
		(
			'error' => FALSE,
			'message' => 'Download complete, ZIP file extracted, WordPress installed'
		);
	}
	catch (Exception $e)
	{
		$status = array
		(
			'error' => TRUE,
			'message' => $e->getMessage()
		);
	}
}

function recursive_move($src,$dst)
{ 
	$dir = opendir($src); 

	@mkdir($dst); 

	while(false !== ($file = readdir($dir)))
	{ 
		if ($file != '.' AND $file != '..')
		{ 
			if (is_dir($src . '/' . $file))
			{ 
				recursive_move($src . '/' . $file,$dst . '/' . $file); 
			} 
			else 
			{ 
				rename($src . '/' . $file,$dst . '/' . $file); 
			} 
		} 
	} 

	closedir($dir); 
} 

function recursive_remove($src)
{ 
	$dir = opendir($src); 

	while(false !== ($file = readdir($dir)))
	{ 
		if ($file != '.' AND $file != '..')
		{ 
			if (is_dir($src . '/' . $file))
			{ 
				recursive_remove($src . '/' . $file); 
			} 
			else 
			{ 
				unlink($src . '/' . $file);
			} 
		} 
	}

	rmdir($src);

	closedir($dir); 
}

// get the latest wordpress version
$version = '';
$contents = @file_get_contents('http://wordpress.org/download/');
if (!empty($contents))
{
	preg_match('/download-button.*>(.*)<\/a/iUS',$contents,$m);
	$version = trim(str_ireplace(array('&nbsp;','download','wordpress'),'',strip_tags($m[1])));
}

$url = !isset($_REQUEST['url']) ? 'http://wordpress.org/latest.zip' : $_REQUEST['url'] ;

$kwf = (isset($_REQUEST['kwf']) AND $_REQUEST['kwf']==1) ? ' checked="checked"' : '' ;

$ds = (empty($_REQUEST) OR (isset($_REQUEST['ds']) AND $_REQUEST['ds']==1)) ? ' checked="checked"' : '' ;

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>WordPress Downloader</title>
	<style>
		html{background:#f7f7f7;}body{color:#333;font-family:"Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif;}#body{background:#fff;margin:2em auto 0 auto;width:700px;padding:1em 2em;-moz-border-radius:11px;-khtml-border-radius:11px;-webkit-border-radius:11px;border-radius:11px;border:1px solid #dfdfdf;}a{color:#2583ad;text-decoration:none;}a:hover{color:#d54e21;}h1{clear:both;color:#666;font:24px Georgia,"Times New Roman",Times,serif;margin:5px 0 0 -4px;padding:0;padding-bottom:7px;}h2{font-size:16px;}p,li{padding-bottom:2px;font-size:12px;line-height:18px;}code{font-size:13px;}ul,ol{padding:5px 5px 5px 22px;}#logo{margin:6px 0 14px 0;border-bottom:none;}.step{margin:20px 0 15px;}.step,th{text-align:left;padding:0;}.submit input,.button,.button-secondary{font-family:"Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif;text-decoration:none;font-size:14px!important;line-height:16px;padding:6px 12px;cursor:pointer;border:1px solid #bbb;color:#464646;-moz-border-radius:15px;-khtml-border-radius:15px;-webkit-border-radius:15px;border-radius:15px;-moz-box-sizing:content-box;-webkit-box-sizing:content-box;-khtml-box-sizing:content-box;box-sizing:content-box;}.button:hover,.button-secondary:hover,.submit input:hover{color:#000;border-color:#666;}.button,.submit input,.button-secondary{background-color:#f2f2f2;}.button:active,.submit input:active,.button-secondary:active{background-color:#eee;}.form-table{border-collapse:collapse;margin-top:1em;width:100%;}.form-table td{margin-bottom:9px;padding:10px;border-bottom:8px solid #fff;font-size:12px;}.form-table th{font-size:13px;text-align:left;padding:16px 10px 10px 10px;border-bottom:8px solid #fff;width:110px;vertical-align:top;}.form-table tr{background:#f3f3f3;}.form-table code{line-height:18px;font-size:18px;}.form-table p{margin:4px 0 0 0;font-size:11px;}.form-table input{line-height:20px;font-size:15px;padding:2px;}#error-page{margin-top:50px;}#error-page p{font-size:12px;line-height:18px;margin:25px 0 20px;}#error-page code{font-family:Consolas,Monaco,Courier,monospace;}label{color:#666;font-weight:bold;display:block;margin-bottom:3px;}span{font-size:11px;color:#999;}.message{background-color:#fff;padding:10px 20px;-moz-border-radius:7px;-khtml-border-radius:7px;-webkit-border-radius:7px;border-radius:7px;}.message.success {background-color:#cfc;color:green;}.message.error{background-color:#fcc;border:0;color:red;}#footer{width:700px;margin:10px auto 0;font-size:11px;text-align:center;color:#ccc;}
	</style>

</head>
<body>

	<div id="body">

	<h1>WordPress Downloader</h1>

	<?php if ($_REQUEST AND !empty($status)): ?>

		<p class="message<?php echo $status['error'] ? ' error' : ' success' ; ?>"><?php echo $status['message']; ?></p>
		<?php if (!$status['error']): ?><p><br/><a class="button" href="<?php echo !empty($wordpress_folder) ? $wordpress_folder : '' ; ?>index.php">Cotniue to configure WordPress...</a></p><?php endif; ?>

	<?php endif; ?>

	<?php if (empty($status) OR $status['error']): ?>

	<p>Lets get started, and hopefully save you a little bit of time!</p>

	<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">

		<p><label>WordPress Release Zip Link</label>
		<input style="width:99%" type="text" name="url" value="<?php echo $url; ?>"/><br/>
		<span>Copy and paste a valid WordPress Zip file link<?php if ($version): ?>, latest WordPress release is <strong><?php echo $version; ?></strong><?php endif; ?></span></p>

		<p>If you need a <a href="http://wordpress.org/download/release-archive/" target="_blank">different WordPress version</a>, browse for the release you want and copy the Zip file link location by right-clicking (<em>or control-clicking</em>) the <code>zip</code> link and choosing "Copy Link Location", paste the link below...</p>

		<p><input type="checkbox" name="kwf" value="1"<?php echo $kwf; ?>> Keep <code>wordpress</code> folder after Zip extraction?</p>

		<p><input type="checkbox" name="ds" value="1"<?php echo $ds; ?>> Delete <code><?php echo $_SERVER['PHP_SELF']; ?></code> when done?</p>

		<p class="submit"><input type="submit" name="submit" value="Download now!"/></p>

	</form>

	<?php endif; ?>

	</div>

	<div id="footer"><a href="http://farinspace.com/2010/04/wordpress-downloader/">WordPress Downloader</a> | <a href="http://farinspace.com/">farinspace web</a></div>

</body>
</html>