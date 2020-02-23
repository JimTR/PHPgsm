<?php
echo '<head><script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<title>Console Log Demo</title>
<script>

function FetchLog() {
//alert(vars);
//if(typeof page_name != "undefined")
var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = value;
    });
    //alert (vars["lines"]);
    var lines = vars["lines"];
 var str = document.getElementById("log").innerHTML;
 //alert (str + lines);
 if ($("#log").is(":empty")) {
 lines = 10;}
 //alert (str + lines);
$.ajax({
 
 url: "readlog.php?path="+vars["path"]+"&lines="+lines,
  type: "get",
  success: function(data){
   // Perform operation on return value
   //alert(data);
   //$("#load").html(data);
   //alert ("data = "+data);
   
  var str = document.getElementById("log").innerHTML;
  //alert(str);
  data = data.replace(/(?:\r\n|\r|\n)/g, "<br>");
  var ld =data.split("<br>");
  //alert("ld = "+ld[ld.length-2]);
  var res = str.split("<br>");
  var Laste = res[res.length-2];
  //alert("Laste "+Laste);
  if (vars["lines"] >1) {
  vars["lines"] = 1;
}
  if (Laste !== ld[ld.length-2]) {
  document.getElementById("log").innerHTML = str+data;
   var element = document.getElementById("log");
        element.scrollTop = element.scrollHeight;
}
  },
  complete:function(data){
   setTimeout(FetchLog,1000);
  }

});
}

function FetchPlayers() {
var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = value;
    });
$.ajax({
 
 url: "viewplayers.php?id=fofserver&type=source&host=46.32.237.232:27015",
  type: "get",
  success: function(data){
   // Perform operation on return value
   //alert(data);
   document.getElementById("player").innerHTML = data;
  },
  complete:function(data){
   setTimeout(FetchPlayers,5000);
  }

});

}
$(document).ready(function(){
    //initializeJS();
    console.log( "ready!" );
    FetchLog();
    FetchPlayers()
 });
</script></head>
<body>
<P>Console For FOFServer</P><div style ="border:1px;border-style: solid;padding:1%;"><div id ="log" style="background:#000;color:#fff;width:55%;height:55%;overflow:scroll;padding:1%;float:left;white-space: nowrap;"></div>
<div id = "player" style ="clear:none;padding:1%;width:40%;float:right;">players</div>
<div style="clear:both;padding:2%;">Send Command&nbsp;&nbsp; <input type="text" name="cmd" value="" size="50">&nbsp;&nbsp;<input type="submit" value="Send"></div></div></body>';
?>
