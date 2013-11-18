/*
function menutoggle(ulid) {
	ul = document.getElementById(ulid)
	x = ul.style.display;
	if(x=='none') ul.style.display='block' else ul.style.display='none'
}
*/
ban = document.getElementById("aplicaciones")
lis = ban.getElementsByTagName('li')
var uls=new Array();
for(i in lis) {
	li = lis[i]
	liid = li.id
	ulid="list-"+liid.substr(5)
	ul = document.getElementById(ulid)
	if(ul) {
		ul.style.display = 'none'
		li.setAttribute('onclick',"menutoggle('"+ulid+"')")
		li.setAttribute('onmouseover',"this.style.cursor='pointer'")
		uls.push(ul)
	}
}

function menutoggle(ulid) {
	for(i in uls) {
		ul=uls[i]
		if(ul.id==ulid) {
			if(ul.style.display=='block')
				ul.style.display='none'
			else
				ul.style.display='block'
		}
		else
			ul.style.display='none'
	}
}
