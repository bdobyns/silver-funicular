<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<meta name="phpWebLog" content="<?php echo $G_VER; ?>">
<meta name="Language" content="<?php echo $LANG; ?>">
<meta name="Layout" content="<?php echo stripslashes($LAYOUT["Description"]); ?>">
<meta name="LayoutVersion" content="<?php echo $LAYOUT["Version"]; ?>">
<meta name="LayoutAuthor" content="<?php echo stripslashes($LAYOUT["Author"]); ?>">
<meta http-equiv="Pragma" content="no-cache">
<title><?php echo stripslashes($CONF["SiteName"]) . " - " . stripslashes($CONF["SiteSlogan"]); ?></title>

<style type = "text/css">
	BODY, TABLE, TD {font-family: <?php echo $LAYOUT["FontFamily"]; ?>; font-size: 10pt; }
	TH {font-family: <?php echo $LAYOUT["FontFamily"]; ?>; text-align: left;}
	A {text-decoration: underline; color: <?php echo $LAYOUT["LinkColor"]; ?>;}
	A:hover {color: <?php echo $LAYOUT["HoverColor"]; ?>; }
	A.nav {color: <?php echo $LAYOUT["NavColor"]; ?>; text-decoration: underline; }
	A.title {color: <?php echo $LAYOUT["TitleColor"]; ?>; }
	.notice {font-family: <?php echo $LAYOUT["FontFamily"]; ?>; font-size: small;}
</style>

<script	language = "javascript"
	type	= "text/javascript"
	src	= "<?php echo $G_URL; ?>/formcheck.inc.js">
</script>

</head>

<body
	text	= "<?php echo $LAYOUT["FgColor"]; ?>"
	bgcolor	= "<?php echo $LAYOUT["BgColor"]; ?>"
	background = "<?php echo $LAYOUT["BgURL"]; ?>"
	link	= "<?php echo $LAYOUT["LinkColor"]; ?>"
	vlink	= "<?php echo $LAYOUT["LinkColor"]; ?>"
	topmargin	= "0"
	leftmargin	= "0"
	marginwidth	= "0"
	marginheight= "0">

<!-- INSERT BANNER CODE HERE -->

<?php
	F_drawHeader();
?>

<!-- inside spread (3) -->
<table	border	= 0
	cellspacing	= 0
	cellpadding	= 0
	align	= center
	width	= "<?php echo $LAYOUT["PageWidth"]; ?>">
<tr>
<td	colspan	= 3
	align	= center><img 
	src	= "<?php echo $G_URL; ?>/images.d/speck.gif"
	width	= 1
	height	= <?php echo $LAYOUT["AreaPadding"]; ?>></td>
</tr>
<tr>

<?php
	F_doBlocks("l");
?>

<td width="100%" valign="top">

<?php 
	/*== display any messages ==*/
        if (!empty($msg)) { F_notice($msg); }
?>
<!-- end header.inc.php -->
