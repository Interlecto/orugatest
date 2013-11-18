
ban = document.getElementById("banner")
pp = document.getElementById("test")
var n=0;
var arr=new Array();
var t;

//ban.innerHTML+=ban.innerHTML
//pp.innerHTML += ban + " ";

for(x in ban.childNodes) {
	child = ban.childNodes[x]
	if(child.innerHTML) {
		//child.innerHTML = '<a href="/">'+child.innerHTML+'</a>';
		child.style.display="none"
		arr[n++] = child
	}
}

var i=0;

function banneritem(c) {
	arr[i].style.display="none";
	if(!c) i=0;
	else if(c=='next') ++i;
	else if(c=='prev') --i;
	else i=c;
	if(i>=n) i=0;
	while(i<0) i+=n;
	arr[i].style.display="block";
	clearTimeout(t);
	t = setTimeout("banneritem('next')",6000);
}

banneritem(i)
