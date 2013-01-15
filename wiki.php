<?php namespace kafene;
/**
 * @package kafene-wiki
 * @copyright 2012 kafene software <http://kafene.org/>
 * @license Public Domain - see http://unlicense.org/
 */

define('TITLE', 'my wiki');
define('DIR', 'posts');
define('EXT', '.md');
define('MAX_BREADCRUMBS', 10);
define('MAX_SIZE', 2097152); # 2097152 = 2MB.
define('SELF', basename(__FILE__));

if(!is_dir(DIR) && !mkdir(DIR))
  throw new \Exception('Directory '.DIR.' not found, and can\'t be created.');

session_start();
$_SESSION['breadcrumb'] = isset($_SESSION['breadcrumb'])
  ? $_SESSION['breadcrumb'] : array();

function filename($file) {
  return DIR.'/'.preg_replace('/[^a-z0-9\-]/', '', strtolower($file)).EXT;
}

# If user is submitting a post to create/edit/delete
if(isset($_POST['text']) && !empty($_POST['title'])) {

  $text = substr(trim($_POST['text']), 0, MAX_SIZE);
  $file = filename( substr($_POST['title'], 0, (128 - strlen(EXT))) );

  # If the text is blank, delete the file.
  if(empty($text)) {
    if(!unlink($file))
      throw new \Exception('Delete failed!');
    else
      exit(header('Location: '.SELF));
  }
  # Otherwise write the file and redirect to it
  else {
    $write = file_put_contents($file, $text);
    if($write)
      exit(header("Location: "
         . SELF."?v=".basename($file, EXT)
    ));
  }

}

$file = isset($_GET['v']) ? $_GET['v'] : false;
$filename = $content = '';

if($file) {
  if(!isset($_SESSION['breadcrumb']))
    $_SESSION['breadcrumb'] = array();
  $_SESSION['breadcrumb'] = array_unique(
    array_merge(array($file), $_SESSION['breadcrumb'])
  );
  if(count($_SESSION['breadcrumb']) > MAX_BREADCRUMBS)
    $_SESSION['breadcrumb'] = array_slice(
      $_SESSION['breadcrumb'], 0, MAX_BREADCRUMBS
    );
  $filename = filename($file);
  if(!is_readable($filename) || !($content = file_get_contents($filename)))
    $content = $filename = '';
}

$title = $filename ? basename($filename, EXT) : '';

?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?= (TITLE.($title ? ' - '.$title : '')) ?></title>
<script src="http://kafene.github.com/asset/marked/marked.min.js"></script>
<script src="http://yandex.st/highlightjs/7.3/highlight.min.js"></script>
<!-- @see http://www.jsdelivr.com/#!codemirror -->
<script src="http://cdn.jsdelivr.net/codemirror/2.36/codemirror.js"></script>
<script src="http://kafene.github.com/asset/codemirror/modes/markdown.js"></script>
<link rel="stylesheet" type="text/css" href="http://cdn.jsdelivr.net/codemirror/2.36/codemirror.css"></script>
<link rel="stylesheet" type="text/css" href="http://codemirror.net/theme/solarized.css"></script>
<link rel="stylesheet" type="text/css" href="http://yandex.st/highlightjs/7.3/styles/github.min.css"></script>
<link rel="stylesheet" type="text/css" href="http://cdnjs.cloudflare.com/ajax/libs/normalize/2.0.1/normalize.css">
<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=PT+Sans:400,700,400italic&subset=latin,latin-ext">
<style>
  * { box-sizing:border-box !important; border-collapse:collapse; }
  html, body { height:100%; margin:0; padding:0; }
  html, body, textarea, input, button {
    font:1em/1.5em "PT Sans","Droid Sans Mono","Segoe UI",sans-serif;
  }
  body { margin:1% auto; max-width:85%; }
  hr { margin:2% 0; padding:0; border:0; border-top:1px solid #ccc; }
  #side, #main { height:100%; display:inline-block; vertical-align:top; }
  #side { width:19%; }
  #main { width:80%; }
  #side ul { list-style:inside disc; padding:0; }
  #side li a { text-decoration:none; font-size:80%; color:#666;}
  a { text-decoration:none; color:#c00; }
  a:hover { text-decoration:underline !important; }
  nav { color:#CCC; }
  nav a, nav b {
    color:#666; font-size:80%; display:inline-block; margin-right:1em;
  }
  h3, h1#fname, nav b { color:#c00; }
  h1, h2, h3, h4, h5, h6 { margin:2% 0; line-height:1; padding:0; }
  div.edit { margin-top:1em; }
  div.edit form { display:none; }
  textarea { font-size:1em; width:100%; }
  input { margin:0.5em 0; }
  input:not([type="submit"]) { width:100%; }
  input[type="submit"] { display:inline-block; }
  input[type="text"], textarea { border:1px solid #ccc; background:#fff; }
  input[type="text"]:focus, textarea:focus { outline:1px solid gold; }
  p { margin:0.5em 0; }
  pre { padding:1px; border:1px solid #ccc; }
  pre, code, kbd, samp, pre code, tt, textarea {
    font-family:"PT Mono","Monaco","Consolas","Droid Sans Mono",monospace;
    background:transparent !important;
  }
  blockquote {
    font-style:italic; color:#333; padding:0 0.5em;
    margin:0 1em; border:1px solid #eef; border-radius:1em;
  }
  div.edit form { height:35em; }
  .CodeMirror { border:1px solid #ccc; height:auto; }
  .CodeMirror-scroll { overflow-x:auto; overflow-y:hidden; }
  .CodeMirror, .CodeMirror-scroll { height:100%; }
</style>
</head><body>
<div id="side">
  <a href="<?= SELF ?>">Home</a><br><br>
  <b>files:</b><br>
  <ul>
  <?php
  foreach(glob(DIR.'/*'.EXT) as $sidebar_file) {
    $sidebar_file = basename($sidebar_file, EXT);
    $sel = ($file == $sidebar_file);
    echo '<li>'
       . ($sel ? '<b>' : '')
       . '<a href="'.(SELF).'?v='.$sidebar_file.'">'.$sidebar_file.'</a>'
       . ($sel ? '</b>' : '')
       . '</li>';
  }
  ?>
  </ul>
</div>
<div id="main">
<nav>
<b>recent:</b>
<?php
  foreach($_SESSION['breadcrumb'] as $filename)
    echo '<a href="'.(SELF).'?v='.$filename.'">'.$filename.'</a>';
?>
</nav>
<h1 id="fname"><?= $title ?></h1>
<div id="butts">
<?= ($content ? '<button class="edit">Edit</button>' : '') ?>
<button class="new">New</button>
<?= ($content ? '<button class="del">Delete</button>' : '') ?>
</div>
<div id="content"><?= ($content ? "<hr>\n".$content : '') ?></div>
<div class="edit"><hr>
<form action="<?= SELF ?>" method="post">
<input type="text" class="title" name="title" value="<?= $title ?>" required>
<textarea class="text" id="kwikitext" name="text"><?= $content ?></textarea>
<small>Note: blanking the text and submitting the form will delete the file.</small><br>
<button class="submit">Save</button>
<button class="cancel">Cancel</button>
<br><br>
</form>
<br><br>
<footer id="footer">
</footer>
</div>
</div>
<script>
  function isArray(a) {
    return !!a && "[object Array]" == str.call(a);
  }
  window.addEventListener("load",function(){
    var d = document;
    var qs  = function(el) { return document.querySelector(el); };
    var qsa = function(el) { return document.querySelectorAll(el); };
    var bedit   = qs("#butts .edit");
    var bnew    = qs("#butts .new");
    var bdel    = qs("#butts .del");
    var bsave   = qs("div.edit button.submit");
    var bcancel = qs("div.edit button.cancel");
    var cmEditor;
    MarkdownAndHighlight();

    function MarkdownAndHighlight() {
      qs("#content").innerHTML = marked(qs("#content").innerHTML);
      if(hljs) {
        hljs.tabReplace = "  ";
        var codes = qsa("pre code");
        if(codes && codes.length){
          for(var i = 0; i < codes.length; i++) {
            var ct = codes[i].innerHTML;
            ct = ct.replace("<", "&lt;").replace(">", "&gt;").trim();
            codes[i].innerHTML = ct;
            hljs.highlightBlock(codes[i]);
            ct = codes[i].innerHTML;
            /* marked translates unmatched < and > to &lt;!-- and --&gt; */
            ct = ct.replace("&lt;!--","&lt;").replace("--&gt;","&gt;");
            codes[i].innerHTML = ct;
          }
        }
      }
    }

    function startCodeMirror() {
      /* http://codemirror.net/doc/manual.html */
      cmEditor = CodeMirror.fromTextArea(qs("#kwikitext"), {
          value:qs("#kwikitext").value /* , theme: "solarized dark" */
        , lineWrapping:true, tabindex:2, autofocus:true, mode: "markdown"
      });
      /* Broken with cdnjs older version of codemirror *//*
        var hlLine = cmEditor.addLineClass(0, "background", "activeline");
        cmEditor.on("cursorActivity", function() {
          var cur = cmEditor.getLineHandle(cmEditor.getCursor().line);
          if(cur != hlLine) {
            cmEditor.removeLineClass(hlLine, "background", "activeline");
            hlLine = cmEditor.addLineClass(cur, "background", "activeline");
          }
        });
      */
      return cmEditor;
    }

    bedit && bedit.addEventListener("click",function(){
      qs("div.edit form").style.display = "block";
      qs("#butts").style.display = "none";
      qs("#kwikitext").focus();
      cmEditor = startCodeMirror();
      qs("#footer").scrollIntoView();
    });

    bnew.addEventListener("click",function(){
      var tas = qs("div.edit form textarea.text");
      var ins = qs("div.edit form input.title");
      ins.value = tas.textContent = "";
      qs("#butts").style.display = "none";
      qs("div.edit form").style.display="block";
      ins.focus();
      var cmEditor = startCodeMirror();
      cmEditor.focus();
    });

    bdel && bdel.addEventListener("click",function(){
      var ta = qs("div.edit form textarea.text");
      ta.textContent = ta.innerHTML = "";
      qs("div.edit form").submit();
    });

    bsave.addEventListener("click",function(ev){
      if(ev.preventDefault) { ev.preventDefault(); }
      cmEditor.save();
      qs("div.edit form").submit();
    });

    bcancel.addEventListener("click",function(ev){
      if(ev.preventDefault) { ev.preventDefault(); }
      /*
      if(cmEditor) { cmEditor.save(); }
      try { cmEditor.toTextArea(); } catch(a){}
      */
      if(cmEditor) { cmEditor = null; }
      var codeMirrors = qsa(".CodeMirror");
      if(codeMirrors) {
        for(var i = 0; i < codeMirrors.length; i++) {
          if(codeMirrors[i].parentNode) {
            codeMirrors[i].parentNode.removeChild(codeMirrors[i]);
          }
        }
      }
      qs("div.edit form").style.display = "none";
      qs("#butts").style.display = "block";
    });
  });
</script>
</body>
</html>