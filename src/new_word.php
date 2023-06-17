<?php

/**************************************************************
"Learning with Texts" (LWT) is free and unencumbered software 
released into the PUBLIC DOMAIN.

Anyone is free to copy, modify, publish, use, compile, sell, or
distribute this software, either in source code form or as a
compiled binary, for any purpose, commercial or non-commercial,
and by any means.

In jurisdictions that recognize copyright laws, the author or
authors of this software dedicate any and all copyright
interest in the software to the public domain. We make this
dedication for the benefit of the public at large and to the 
detriment of our heirs and successors. We intend this 
dedication to be an overt act of relinquishment in perpetuity
of all present and future rights to this software under
copyright law.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE 
WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE
AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS BE LIABLE 
FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN 
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN 
THE SOFTWARE.

For more information, please refer to [http://unlicense.org/].
***************************************************************/

/**************************************************************
Call: new_word.php?...
			... text=[textid]&lang=[langid] ... new term input  
			... op=Save ... do the insert
New word, created while reading or testing
***************************************************************/

require_once( 'settings.inc.php' );
require_once( 'connect.inc.php' );
require_once( 'dbutils.inc.php' );
require_once( 'utilities.inc.php' );
require_once( 'simterms.inc.php' );

// INSERT

if (isset($_REQUEST['op'])) {
	
	if ($_REQUEST['op'] == 'Save') {

		$text = trim(prepare_textdata($_REQUEST["WoText"]));
		$textlc = mb_strtolower($text, 'UTF-8');
		$translation_raw = repl_tab_nl(getreq("WoTranslation"));
		if ( $translation_raw == '' ) $translation = '*';
		else $translation = $translation_raw;
	
		$titletext = "New Term: " . tohtml($textlc);
		pagestart_nobody($titletext);
		echo '<h4><span class="bigger">' . $titletext . '</span></h4>';
	
		$message = runsql('insert into ' . $tbpref . 'words (WoLgID, WoTextLC, WoText, ' .
			'WoStatus, WoTranslation, WoSentence, WoRomanization, WoStatusChanged,' .  make_score_random_insert_update('iv') . ') values( ' . 
			$_REQUEST["WoLgID"] . ', ' .
			convert_string_to_sqlsyntax($textlc) . ', ' .
			convert_string_to_sqlsyntax($text) . ', ' .
			$_REQUEST["WoStatus"] . ', ' .
			convert_string_to_sqlsyntax($translation) . ', ' .
			convert_string_to_sqlsyntax(repl_tab_nl($_REQUEST["WoSentence"])) . ', ' .
			convert_string_to_sqlsyntax($_REQUEST["WoRomanization"]) . ', NOW(), ' .  
make_score_random_insert_update('id') . ')', "Term saved", $sqlerrdie = FALSE);

		if (substr($message,0,22) == 'Error: Duplicate entry') {
			$message = 'Error: <b>Duplicate entry for <i>' . $textlc . '</i></b><br /><br /><input type="button" value="&lt;&lt; Back" onclick="history.back();" />';
		}
		
		$wid = get_last_key();

		$hex = strToClassName(prepare_textdata($textlc));

		saveWordTags($wid);
		
		$showAll = getSettingZeroOrOne('showallwords',1);
?>

<p><?php echo $message; ?></p>

<?php
		if (substr($message,0,5) != 'Error') {
?>
	
<script type="text/javascript">
//<![CDATA[
var context = window.parent.frames['l'].document;
var contexth = window.parent.frames['h'].document;
var woid = <?php echo prepare_textdata_js($wid); ?>;
var status = <?php echo prepare_textdata_js($_REQUEST["WoStatus"]); ?>;
var trans = <?php echo prepare_textdata_js($translation . getWordTagList($wid,' ',1,0)); ?>;
var roman = <?php echo prepare_textdata_js($_REQUEST["WoRomanization"]); ?>;
var title = make_tooltip(<?php echo prepare_textdata_js($text); ?>,trans,roman,status);
$('.TERM<?php echo $hex; ?>', context).removeClass('status0 hide').addClass('word' + woid + ' ' + 'status' + status).attr('data_trans',trans).attr('data_rom',roman).attr('data_status',status).attr('data_wid',woid).attr('title',title);
$('#learnstatus', contexth).html('<?php echo texttodocount2($_REQUEST['tid']); ?>');
window.parent.frames['l'].focus();
window.parent.frames['l'].setTimeout('cClick()', 100);
<?php 
		if (! $showAll) echo refreshText($text,$_REQUEST['tid']);
?>
//]]>
</script>
	
<?php
		} // (substr($message,0,5) != 'Error')

	} // $_REQUEST['op'] == 'Save'

} // if (isset($_REQUEST['op']))

// FORM

else {  // if (! isset($_REQUEST['op']))

	// new_word.php?text=..&lang=..
	
	$lang = getreq('lang') + 0;
	$text = getreq('text') + 0;
	pagestart_nobody('');
?>
<script type="text/javascript" src="js/unloadformcheck.js" charset="utf-8"></script>
<?php
	$scrdir = getScriptDirectionTag($lang);
	
?>
	
	<form name="newword" class="validate" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
	<input type="hidden" name="WoLgID" id="langfield" value="<?php echo $lang; ?>" />
	<input type="hidden" name="tid" value="<?php echo $text; ?>" />
	<table class="tab3" cellspacing="0" cellpadding="5">
	<tr>
	<td class="td1 right"><b>New Term:</b></td>
	<td class="td1"><input <?php echo $scrdir; ?> class="notempty setfocus checkoutsidebmp" data_info="New Term" type="text" name="WoText" id="wordfield" value="" maxlength="250" size="35" /> <img src="icn/status-busy.png" title="Field must not be empty" alt="Field must not be empty" /></td>
	</tr>
	<?php print_similar_terms_tabrow(); ?>
  <tr>
	<td class="td1 right">Translation:</td>
	<td class="td1"><textarea class="textarea-noreturn checklength checkoutsidebmp" data_maxlength="500" data_info="Translation" name="WoTranslation" cols="35" rows="3"></textarea></td>
	</tr>
	<tr>
	<td class="td1 right">Tags:</td>
	<td class="td1">
	<?php echo getWordTags(0); ?>
	</td>
	</tr>
	<tr>
	<td class="td1 right">Romaniz.:</td>
	<td class="td1"><input type="text" class="checkoutsidebmp" data_info="Romanization" name="WoRomanization" value="" maxlength="100" size="35" /></td>
	</tr>
	<tr>
	<td class="td1 right">Sentence<br />Term in {...}:</td>
	<td class="td1"><textarea <?php echo $scrdir; ?> name="WoSentence" cols="35" rows="3" class="textarea-noreturn checklength checkoutsidebmp" data_maxlength="1000" data_info="Sentence"></textarea></td>
	</tr>
	<tr>
	<td class="td1 right">Status:</td>
	<td class="td1">
	<?php echo get_wordstatus_radiooptions(1); ?>
	</td>
	</tr>
	<tr>
	<td class="td1 right" colspan="2">  &nbsp;
	<?php echo createDictLinksInEditWin3($lang,'document.forms[\'newword\'].WoSentence','document.forms[\'newword\'].WoText'); ?>
	&nbsp; &nbsp;
	<input type="submit" name="op" value="Save" /></td>
	</tr>
	</table>
	</form>

<?php

}

pageend();

?>