<?php
ob_start();

if(module_exists('blog')) {
	$d=dir('data/notes');
	$dd = array();
	while (false !== ($entry = $d->read())) {
		if(substr($entry,-5)==".note") {
			$dd[] = substr($entry,0,-5);
		}
	}
	$d->close();
	rsort($dd);
?>
	<section><h2><a href="/notas/">Últimos artículos</a></h2>
<?php
	foreach($dd as $i=>$n) {
		if($i>=5) break;
		$ar=array();
		dat2array("data/notes/$n.note",$ar);
		$ntitle = isset($ar['title'])&&$ar['title'] ? $ar['title'] : (
			isset($ar['status'])&&$ar['status'] ? $ar['status'] : $n);
		$nmid = str2uri($ntitle);
		$nlink = "/notas/$nmid/$n.html";
?>
		<article id=article-<?php echo $i?>>
			<h3><a href="<?php echo $nlink?>"><?php echo $ntitle?></a></h3>
			<?php echo articlecontent($n,$ar,"",true) ?>

		</article>
<?php
	}
?>
	</section>
<?php
}
?>
<script charset="utf-8" src="http://widgets.twimg.com/j/2/widget.js"></script>
<script>
new TWTR.Widget({
  version: 2,
  type: 'profile',
  rpp: 4,
  interval: 30000,
  width: 200,
  height: 300,
  theme: {
    shell: {
      background: '#ddbb33',
      color: '#0033ff'
    },
    tweets: {
      background: '#efece0',
      color: '#000000',
      links: '#0022aa'
    }
  },
  features: {
    scrollbar: false,
    loop: false,
    live: false,
    behavior: 'all'
  }
}).render().setUser('OrugaAmarilla').start();
</script>
<?php
return ob_get_clean();
?>