$.widget( "ui.timespinner", $.ui.spinner, {
	options: {
		step: 60 * 1000,
		page: 60
    },
	_parse: function( value ) {
		if ( typeof value === "string" ) {
			if ( Number( value ) == value ) {
				return Number( value );
			}
			return +Globalize.parseDate( value );
		}
		return value;
	},
	_format: function( value ) {
		return Globalize.format( new Date(value), "t" );
	}
});
function ymdDate(d) {
	//return new Date(d);
	var ds = d.split("-");
	return new Date(ds[0], -1+ds[1], ds[2]);
}
function ymdDateAdd(d,dd) {
	var dc = new Date(d);
	var ds = dc.getTime();
	var rs = ds + 1000*60*60*24*dd;
	var rc = new Date(rs);
	return $.datepicker.formatDate("yy-mm-dd",rc);
}
function ymdTimeAdd(d,t,ss) {
	var dc = new Date(""+d+" "+t);
	var ds = dc.getTime();
	var rs = ds + 1000*ss;
	var rc = new Date(rs);
	return rc.getTime()/1000;
}
function ymdDelta(d1,t1,d2,t2) {
	//var ds1 = d1.split(/-/)
	//var ts1 = t1.split(/:/)
	//var dc1 = new Date(ds1[0],-1-ds1[1],ds1[2],ts1[0],ts1[1],0);
	var dc1 = new Date(""+d1+" "+t1);
	var tc1 = dc1.getTime();
	//var ds2 = d2.split(/-/)
	//var ts2 = t2.split(/:/)
	//var dc2 = new Date(ds2[0],-1-ds2[1],ds2[2],ts2[0],ts2[1],0);
	var dc2 = new Date(""+d2+" "+t2);
	var tc2 = dc2.getTime();
	$("#alert").text('time '+(tc1/1000)+'..'+(tc2/1000));
	return (tc2-tc1)/1000;
}
function tsHourMins(ts) {
	var mins = Math.round(ts/60)%1440;
	var hours = Math.floor(mins/60);
	var hh = "";
	if(hours<10) hh = "0"+hours+":";
	else hh = ""+hours+":";
	mins -= 60*hours;
	if(mins<10) hh+= "0"+mins;
	else hh+= mins;
	return hh;
}
 
$(function(){
	$("#tabs").tabs({heightStyle:"auto"});
	$(".datepick").datepicker({
		dateFormat: "yy-mm-dd",
		showOn: "button",
		buttonImage: "/images/calendar.png",
		buttonImageOnly: true,
		dayNamesMin: ["Do","Lu","Ma","Mi","Ju","Vi","Sa"],
		monthNames: [ "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre" ],
		prevText: "Anterior",
		nextText: "Siguiente",
		yearRange: "2012:+00"
	});
	$(".timesel").timespinner();
	$("#theday,#weekbegin,#monthbegin,#yearbegin").datepicker("option","altField","#beginDate");
	$("#weekbegin").change(function(){
		$("#weekends,#endDate").val(ymdDateAdd($(this).val(),6));
		$("#beginTime").val("00:00");
		$("#endTime").val("24:00");
	});
	$("#weekends").change(function(){
		$("#weekbegin,#beginDate").val(ymdDateAdd($(this).val(),-6));
		$("#beginTime").val("00:00");
		$("#endTime").val("24:00");
	});
	$("#monthbegin").change(function(){
		$("#monthends,#enndDate").val(ymdDateAdd($(this).val(),29));
		$("#beginTime").val("00:00");
		$("#endTime").val("24:00");
	});
	$("#monthends").change(function(){
		$("#monthbegin,#beginDate").val(ymdDateAdd($(this).val(),-29));
		$("#beginTime").val("00:00");
		$("#endTime").val("24:00");
	});
	$("#yearbegin").change(function(){
		$("#yearends,#endDate").val(ymdDateAdd($(this).val(),364));
		$("#beginTime").val("00:00");
		$("#endTime").val("24:00");
		$("#endTime").change();
	});
	$("#yearends").change(function(){
		$("#yearbegin,#beginDate").val(ymdDateAdd($(this).val(),-364));
		$("#beginTime").val("00:00");
		$("#endTime").val("24:00");
		$("#endTime").change();
	});
	$("#beginDate,#beginTime").change(function(){
		var delta = ymdDelta( $("#beginDate").val(), $("#beginTime").val(), $("#endDate").val(), $("#endTime").val() );
		$("#alert").text($("#alert").text()+". "+delta);

		var periods = +$("#count").val();
		if(periods < 7) { periods = 7; $("#count").val(periods); }
		var range = delta/periods;
		$("#alert").text($("#alert").text()+" = "+periods+" × "+range);
		var ru = 1;
		if(range <= 300) { $("#runits").val("sec"); }
		else if(range <= 3600) { ru=60; $("#runits").val("min"); }
		else if(range <= 43200) { ru=300; $("#runits").val(ru); }
		else if(range <= 86400*4) { ru=3600; $("#runits").val("hour"); }
		else if(range <= 86400*14) { ru=10800; $("#runits").val(ru); }
		else if(range <= 86400*60) { ru=86400; $("#runits").val("day"); }
		else if(range <= 86400*400) { ru=86400*7; $("#runits").val("week"); }
		else { ru=86400*30; $("#runits").val("month"); }
		range = Math.round(range/ru);
		$("#alert").text($("#alert").text()+" ~= "+periods+" × "+ru+" × "+range);
		$("#range").val(range);
		delta = range*ru*periods;
		$("#alert").text($("#alert").text()+" = "+delta);

		var lu = 300;
		if(delta <= 7200) { $("#lunits").val(lu); } 
		else if(delta <= 86400) { lu = 3600; $("#lunits").val("hour"); } 
		else if(delta <= 86400*6) { lu = 10800; $("#lunits").val(10800); } 
		else if(delta <= 86400*35) { lu = 86400; $("#lunits").val("day"); } 
		else if(delta <= 86400*360) { lu = 86400*7; $("#lunits").val("week"); } 
		else if(delta <= 86400*1080) { lu = 86400*30; $("#lunits").val("month"); } 
		else { lu = 86400*365; $("#lunits").val("year"); }
		var lapse = Math.round(delta/lu);
		$("#alert").text($("#alert").text()+" ~= "+lu+" × "+lapse);
		$("#lapse").val(lapse);
		delta = lapse*lu;
		$("#alert").text($("#alert").text()+" = "+delta);
		
		var t2 = ymdTimeAdd( $("#beginDate").val(), $("#beginTime").val(), delta );
		$("#alert").text($("#alert").text()+" => "+t2);
		$("#endDate").val($.datepicker.formatDate("yy-mm-dd",new Date(t2*1000)));
		$("#endTime").val(tsHourMins(t2));
	});
	$("#endDate,#endTime").change(function(){
		var delta = ymdDelta( $("#beginDate").val(), $("#beginTime").val(), $("#endDate").val(), $("#endTime").val() );

		var periods = +$("#count").val();
		if(periods < 7) { periods = 7; $("#count").val(periods); }
		var range = delta/periods;
		var ru = 1;
		if(range <= 300) { $("#runits").val("sec"); }
		else if(range <= 3600) { ru=60; $("#runits").val("min"); }
		else if(range <= 43200) { ru=300; $("#runits").val(ru); }
		else if(range <= 86400*4) { ru=3600; $("#runits").val("hour"); }
		else if(range <= 86400*14) { ru=10800; $("#runits").val(ru); }
		else if(range <= 86400*60) { ru=86400; $("#runits").val("day"); }
		else if(range <= 86400*400) { ru=86400*7; $("#runits").val("week"); }
		else { ru=86400*30; $("#runits").val("month"); }
		range = Math.round(range/ru);
		$("#range").val(range);
		delta = range*ru*periods;

		var lu = 300;
		if(delta <= 7200) { $("#lunits").val(lu); } 
		else if(delta <= 86400) { lu = 3600; $("#lunits").val("hour"); } 
		else if(delta <= 86400*6) { lu = 10800; $("#lunits").val(10800); } 
		else if(delta <= 86400*35) { lu = 86400; $("#lunits").val("day"); } 
		else if(delta <= 86400*360) { lu = 86400*7; $("#lunits").val("week"); } 
		else if(delta <= 86400*1080) { lu = 86400*30; $("#lunits").val("month"); } 
		else { lu = 86400*365; $("#lunits").val("year"); }
		var lapse = Math.round(delta/lu);
		$("#lapse").val(lapse);
		delta = lapse*ru;
		
		var t2 = ymdTimeAdd( $("#beginDate").val(), $("#beginTime").val(), -delta );
		var ts2 = t2 % 86400;
		
		$("#beginDate").val($.datepicker.formatDate("yy-mm-dd",t2));
		$("#beginTime").val(""+(ts2/3600)+":"+((ts2%3600)/60));
	});
});
function set_dates(d1,d2) {
	var active = $("#tabs").tabs("option","active");
	$("[name=date]").val(d1);
	$("[name=enddate]").val(d2);
	$("[name=time]").val("00:00");
	$("[name=endtime]").val("24:00");
	/*switch(active) {
	case 0:
		$("#theday").val(d1);
		$("#fromtime").val("00:00");
		break;
	case 1:
		$("#weekbegin").val(d1);
		$("#weekends").val(d2);
		break;
	case 2:
		$("#monthbegin").val(d1);
		$("#monthends").val(d2);
		break;
	case 3:
		$("#yearbegin").val(d1);
		$("#yearends").val(d2);
		break;
	}*/
};
function set_times(d1,t1,d2,t2) {
	var active = $("#tabs").tabs("option","active");
	$("[name=date]").val(d1);
	$("[name=enddate]").val(d2);
	$("[name=time]").val(t1);
	$("[name=endtime]").val(t2);
	/*switch(active) {
	case 0:
		$("#theday").val(d1);
		$("#fromtime").val(t1);
		break;
	case 1:
		$("#weekbegin").val(d1);
		$("#weekends").val(d2);
		break;
	case 2:
		$("#monthbegin").val(d1);
		$("#monthends").val(d2);
		break;
	case 3:
		$("#yearbegin").val(d1);
		$("#yearends").val(d2);
		break;
	}*/
};
function tabActivate(tab) {
	alert("Activando "+tab)
	$("#tabs").tabs("option","active",tab);
	switch(tab) {
	case 0:
		$("#doday").click();
		break;
	case 1:
		$("#doweek").click();
		break;
	case 2:
		$("#domonth").click();
		break;
	case 3:
		$("#doyear").click();
		break;
	}
}
