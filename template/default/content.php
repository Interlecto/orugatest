/********************************************
**
*********************************************/

#content{font-size:12pt;color:#036;}
#content :visited,#content :link{color:#039;text-decoration:none;}
#content :visited:hover,#content :link:hover{color:#06f;margin-bottom:-1px;border-bottom:1px dotted #60f;text-decoration:none}
#content{padding:0 1ex 1ex;}
#content p{margin:1ex 0;text-align:justify;}
#content h1{margin:1.5ex 0 .6ex;font-size:2em;}
#content h2{margin:1.5ex 0 .6ex;font-size:1.5em;}
#content h3{margin:1.5ex 0 .6ex;font-size:1.2em;}
#content h4{margin:1.5ex 0 .6ex;font-size:1em;}
#content h5{margin:1.5ex 0 .6ex;font-size:0.9em;}
#content h6{margin:1.5ex 0 .6ex;font-size:0.7em;}
#content :first-child{margin-top:0;}
#content p:first-child:first-letter{display:block;font-size:2.4em;float:left;margin:.15em 0 0 .1em;}

#content th{vertical-align:top}
#content td{vertical-align:top}


#portal{text-align:center;}
#portal #registerform{display:block;float:right;text-align:right;margin:1em;}
#portal #presentation{float:left;width:320px;height:180px;margin:1em;}
#portal #articulos{float:right;width:200px;text-align:left;font-size:10pt;}
#portal #articulos article{margin:1em 0;}
#portal #web{float:left;width:510px;height:360px;margin:1em;background:url('images/red-oruga-amarilla.png') top left no-repeat;}
#portal .portalitem{margin-left:410px;}
#portal .portalitem a{display:block;width:90px;margin-bottom:5px;height:45px;padding:4px;border-radius:4px;border:1px solid #ccc;overflow:hidden;font-size:10pt;}
#portal .portalitem a:hover{border:1px solid #ccf;margin-bottom:5px;}

#portal .portallist{float:left;margin:5px;width:410px;}

#aplicaciones ul{border:1px solid #000;border-style:solid solid none;list-style:none;margin:1ex;padding:0;}
#aplicaciones a,#aplicaciones span{display:block;border-bottom:1px solid #000;padding:1ex;background:#fea;}
#aplicaciones li ul{border-style:none none solid;margin:0;}
#aplicaciones li li a,#aplicaciones li li span{border:none;background:#fff;padding:.5ex;}
#aplicaciones li li a:hover{background:#eee;}

#bits {clear:both;margin:0 0 25px;}
.bit{float:left;width:203px;padding:0 35px 0 0;}
.bit h4{margin:0 0 18px;}
.bit img{margin:0 0 8px;}
.bit p{font-size:9pt;}
.bit.last{padding-right:0;}
.bit .more{margin:0;}

.maq-full{border:1px solid #000;border-collapse:collapse;}
.maq-full td.uno{border:1px solid #aaa;border-style:solid none solid solid;font-weight:bold;width:10em;}
.maq-full td.dos{border:1px solid #aaa;border-style:solid solid solid none;width:12em;}
.maq-full td.blank{background:#eee;border:1px solid #aaa;}

.gallery-frame{position:relative;width:300px;float:left;margin-right:1ex;}
.gallery-panel{position:absolute;width:300px;height:225px;}
.gallery-panel img{padding:0;width:300px;height:225px;}
.gallery{padding:230px 0 0;list-style:none;}
.gallery li{float:left;width:50px;height:50px;}
.gallery .thumb{width:48px;height:48px;}
.gallery .midsize{width:300px;height:225px;position:absolute;top:0;left:0;display:none}
.gallery li:hover .midsize{display:block;}
.galeria #content{text-align:center;}
.galeria .gallery-frame{margin:5px auto 0;float:none;}
.galeria .gallery{margin:0 -150px;}

#maq-edit{float:left;}
#photo-upload{float:left;width:300px;}
#photo-upload form{clear:both;text-align:center;}
.editentry{font-size:.9em;}

.repuesto,
.maquina{position:relative;width:200px;height:150px;float:left;border-radius:5px;overflow:hidden;margin:0 5px 5px 0;}
.entryicon{position:absolute;width:200px;height:150px;top:0;left:0;}
.entrytitle{display:block;position:absolute;text-align:center;top:0;left:0;right:0;padding:4px;font-size:1.1em;background:#000;color:#fff;opacity:.4;}
.entrytext{display:block;position:absolute;text-align:center;top:35px;left:0;right:0;bottom:35px;padding:4px;color:#000}
.entryprice{display:block;position:absolute;text-align:center;bottom:0;left:0;right:0;padding:3px;font-style:normal;color:#000;}
.maquina:hover .entrytitle,
.repuesto:hover .entrytitle{opacity:.6;}

.breadcrumbs{font-size:.9em;margin:0 0 1ex;}
.prelinks{font-size:.9em;margin:0 0 1ex;}
.postlinks{font-size:.9em;margin:0 0 1ex;}

#maqs{list-style:none;padding:0;margin:0;width:100%;height:2em;font-size:.9em;margin:0 0 2em;position:relative;z-index:100}
#maqs li.maq0{display:block;float:left;position:relative;}
#maqs li.maq0 a{display:block;padding:2px 3px;}
#maqs li.maq0 ul{position:absolute;list-style:none;margin:0;padding:0;left:1em;display:none;background:#eee;border:1px #ccc solid;}
#maqs li.maq0 li{padding:0;}
#maqs li.maq0 li a{padding:0 3px;}
#maqs li.maq0:hover{background:#eee;}
#maqs li.maq0:hover>ul{display:block;}

.maqss{list-style:none;padding:0;margin:0;width:100%;height:2em;font-size:.9em;margin:0 0 2em;position:relative;z-index:100}
.maqss li{display:block;float:left;position:relative;}
.maqss li a{display:block;padding:2px 3px;}
.maqss li:hover{background:#eee;}

.dir-listing{list-style:square url("images/file/file.png");}
.dir-listing .folder{list-style-image:url("images/file/folder.png");}
.dir-listing .file-png{list-style-image:url("images/file/file-png.png");}
.dir-listing .file-jpeg,
.dir-listing .file-jpg{list-style-image:url("images/file/file-jpeg.png");}
.dir-listing .file-gif{list-style-image:url("images/file/file-gif.png");}

.usrlong{width:10em;font-weight:bold;}
.usrname{font-size:.9em;}

textarea{width:95%;height:16em;}

#dns{float:left;padding:1px 1ex}
#dns a{display:block;border:1px solid #aaa;border-radius:.5ex;padding:.5ex 1ex;background:rgba(0,0,0,.2);color:#000;box-shadow:.5ex .5ex 1ex
rgba(0,0,0,.5),inset .5ex .5ex .5ex rgba(255,255,255,.5),inset -.5ex -.5ex .5ex rgba(0,0,0,.5)}
#content #dns a:hover{border:1px solid #aaa;box-shadow:.5ex .5ex 1ex
rgba(0,0,0,.5),inset .25ex .25ex .25ex rgba(255,255,255,.5),inset -.25ex -.25ex .25ex rgba(0,0,0,.5)}
