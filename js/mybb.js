function initializeJS() {

    //tool tips
    jQuery('.tooltips').tooltip();

    //popovers
    jQuery('.popovers').popover();

    //custom scrollbar
        //for html
    jQuery("html").niceScroll({styler:"fb",cursorcolor:"#007AFF", cursorwidth: '6', cursorborderradius: '10px', background: '#F7F7F7', cursorborder: '', zindex: '1000'});
        //for sidebar
    jQuery("#sidebar").niceScroll({styler:"fb",cursorcolor:"#007AFF", cursorwidth: '3', cursorborderradius: '10px', background: '#F7F7F7', cursorborder: ''});
        // for scroll panel
    jQuery(".scroll-panel").niceScroll({styler:"fb",cursorcolor:"#007AFF", cursorwidth: '3', cursorborderradius: '10px', background: '#F7F7F7', cursorborder: ''});
    
    //sidebar dropdown menu
    jQuery('#sidebar .sub-menu > a').click(function () {
        var last = jQuery('.sub-menu.open', jQuery('#sidebar'));        
        jQuery('.menu-arrow').removeClass('arrow_carrot-right');
        jQuery('.sub', last).slideUp(200);
        var sub = jQuery(this).next();
        if (sub.is(":visible")) {
            jQuery('.menu-arrow').addClass('arrow_carrot-right');            
            sub.slideUp(200);
        } else {
            jQuery('.menu-arrow').addClass('arrow_carrot-down');            
            sub.slideDown(200);
        }
        var o = (jQuery(this).offset());
        diff = 200 - o.top;
        if(diff>0)
            jQuery("#sidebar").scrollTo("-="+Math.abs(diff),500);
        else
            jQuery("#sidebar").scrollTo("+="+Math.abs(diff),500);
    });

    // sidebar menu toggle
    jQuery(function() {
        function responsiveView() {
            var wSize = jQuery(window).width();
            if (wSize <= 768) {
                jQuery('#container').addClass('sidebar-close');
                jQuery('#sidebar > ul').hide();
            }

            if (wSize > 768) {
                jQuery('#container').removeClass('sidebar-close');
                jQuery('#sidebar > ul').show();
            }
        }
        jQuery(window).on('load', responsiveView);
        jQuery(window).on('resize', responsiveView);
    });

    jQuery('.toggle-nav').click(function () {
        if (jQuery('#sidebar > ul').is(":visible") === true) {
            jQuery('#main-content').css({
                'margin-left': '0px'
            });
            jQuery('#sidebar').css({
                'margin-left': '-180px'
            });
            jQuery('#sidebar > ul').hide();
            jQuery("#container").addClass("sidebar-closed");
        } else {
            jQuery('#main-content').css({
                'margin-left': '180px'
            });
            jQuery('#sidebar > ul').show();
            jQuery('#sidebar').css({
                'margin-left': '0'
            });
            jQuery("#container").removeClass("sidebar-closed");
        }
    });

    //bar chart
    if (jQuery(".custom-custom-bar-chart")) {
        jQuery(".bar").each(function () {
            var i = jQuery(this).find(".value").html();
            jQuery(this).find(".value").html("");
            jQuery(this).find(".value").animate({
                height: i
            }, 2000)
        })
    }

}

jQuery(document).ready(function(){
    
    updateClock();
    setInterval('updateClock()', 1000);
    //fetchboot();
    //fetchload();
    fetchgames();
    fetchservers();	
	setInterval('fetchgames()', 60000);
});

function updateClock ()
    {
    var currentTime = new Date ( );
    var currentHours = currentTime.getHours ( );
    var currentMinutes = currentTime.getMinutes ( );
    var currentSeconds = currentTime.getSeconds ( );
 
    // Pad the minutes and seconds with leading zeros, if required
    currentMinutes = ( currentMinutes < 10 ? "0" : "" ) + currentMinutes;
    currentSeconds = ( currentSeconds < 10 ? "0" : "" ) + currentSeconds;
 
    // Choose either "AM" or "PM" as appropriate
    var timeOfDay = ( currentHours < 12 ) ? "AM" : "PM";
 
    // Convert the hours component to 12-hour format if needed
    currentHours = ( currentHours > 12 ) ? currentHours - 12 : currentHours;
 
    // Convert an hours component of "0" to "12"
    currentHours = ( currentHours == 0 ) ? 12 : currentHours;
 
    // Compose the string for display
    var currentTimeString = currentHours + ":" + currentMinutes + ":" + currentSeconds + " " + timeOfDay;
     
     
    $("#clock").html(currentTimeString);
    // $('#clock').css('color','red');
         
 }

function fetchgames(){
 $.ajax({
 url: 'ajax.php?action=rgames',
  type: 'post',
  success: function(data){
   // Perform operation on return value
   //alert(data);
   $("#rgames").html(data);
  },
  complete:function(data){
   //setTimeout(fetchgames,13000);
  }
 });
}
function fetchservers(){
 $.ajax({
 url: 'https://noideersoftware.co.uk:7862/xml.php',
  type: 'post',
  dataType: "xml" ,
  success: function(xml,status){
   
    $(xml).find('Servers').children('base_server').each(function(){
	 var fname = $(this).find('fname').text(); //element name important	
	
	 	 var bfname = fname;

     $("#boot"+fname).html($(this).find('uptime').text());
     $("#load"+fname).html($(this).find('load').text());
     $("#memtotal"+fname).html($(this).find('memTotal').text());
     $("#memfree"+fname).html($(this).find('memfree').text());
     $("#memcached"+fname).html($(this).find('memcache').text());
     $("#memactive"+fname).html($(this).find('memactive').text());
     $("#cpu_model"+fname).html($(this).find('cpu_model').text());
     $("#cpu_processors"+fname).html($(this).find('cpu_processors').text());
     $("#cpu_cores"+fname).html($(this).find('cpu_cores').text());
     $("#cpu_speed"+fname).html($(this).find('cpu_speed').text());
     $("#cpu_cache"+fname).html($(this).find('cpu_cache').text());
     $("#ip"+fname).html( $(this).find('ip').text());
     $("#boot_filesystem"+fname).html( $(this).find('boot_filesystem').text());
     $("#boot_mount"+fname).html($(this).find('boot_mount').text());
     $("#boot_size"+fname).html($(this).find('boot_size').text());
     $("#boot_used"+fname).html( $(this).find('boot_used').text());
     $("#boot_free"+fname).html($(this).find('boot_free').text());
     $("#swaptotal"+fname).html( $(this).find('swaptotal').text());
     $("#swapfree"+fname).html( $(this).find('swapfree').text());
     $("#swapcache"+fname).html($(this).find('swapfree').text());
     $("#distro"+fname).html($(this).find('distro').text());
     $("#kernel"+fname).html($(this).find('kernel').text());
     $("#hname"+fname).html( $(this).find('name').text());
     $("#php"+fname).html($(this).find('php').text());
     $("#screen"+fname).html($(this).find('screen').text());
     $("#apache"+fname).html($(this).find('apache').text());
     $("#glibc"+fname).html( $(this).find('glibc').text());
     $("#mysql"+fname).html($(this).find('mysql').text());
     $("#curl"+fname).html($(this).find('curl').text());
     $("#nginx"+fname).html($(this).find('nginx').text());
     $("#quota"+fname).html($(this).find('quota').text());
     $("#postfix"+fname).html($(this).find('postfix').text());
     $("#reboot"+fname).html( $(this).find('reboot').text());
	 $("#process"+fname).html($(this).find('process').text());
	 $("#gamespace"+fname).html($(this).find('gamespace').text());
	 $("#live_servers"+fname).html($(this).find('live_games').text());
	 $("#total_servers"+fname).html($(this).find('total_games').text());	
     $("#total_mem"+fname).html($(this).find('total_mem').text());
     $("#total_cpu"+fname).html($(this).find('total_cpu').text());
     $("#cpu_pbs"+fname).html($(this).find('total_cpu').text());
     $("#cpu_pb"+fname).attr('aria-valuenow',($(this).find('total_cpu').text()));
     $("#cpu_pb"+fname).css('width',($(this).find('total_cpu').text()));
     $("#mem_pbs"+fname).html($(this).find('total_mem').text());
     $("#mem_pb"+fname).attr('aria-valuenow',($(this).find('total_mem').text()));
     $("#mem_pb"+fname).css('width',($(this).find('total_mem').text()));
     $("#ud_pbs"+fname).html($(this).find('gamespace').text());
     $("#ud_pb"+fname).attr('aria-valuenow',($(this).find('quota_pc').text()+' ('+($(this).find('quota_pc').text()+'%')));
     $("#ud_pb"+fname).attr('aria-valuemax',($(this).find('quota_a').text()));
     $("#ud_pb"+fname).css('width',($(this).find('quota_pc').text()+'%'));
     $("#gs_pbs"+fname).html($(this).find('live_games').text()+'/'+$(this).find('total_games').text());
     $('#gs_pbs'+fname).width($('#gs_pb'+fname).parent().width());
     $('#ud_pbs'+fname).width($('#ud_pb'+fname).parent().width());
     $("#tmem_pbs"+fname).html('Free ('+($(this).find('memfree').text())+')');
     $("#tmem_pb"+fname).css('width',100-parseInt(($(this).find('memfree_pc').text()))+'%');
     $('#tmem_pbs'+fname).width($('#tmem_pb'+fname).parent().width());
      $("#smem_pbs"+fname).html('Free ('+($(this).find('swapfree').text())+')');
     $("#smem_pb"+fname).css('width',100-parseInt(($(this).find('swapfree_pc').text()))+'%');
     $('#smem_pbs'+fname).width($('#smem_pb'+fname).parent().width());
     $('#mem_pbs'+fname).width($('#mem_pb'+fname).parent().width());
     $('#cpu_pbs'+fname).width($('#cpu_pb'+fname).parent().width());
     $('#op_pbs'+fname).width($('#op_pb'+fname).parent().width());
     changeClass( 'cpu_pb'+fname,parseInt($(this).find('total_cpu').text()));
     changeClass( 'mem_pb'+fname,parseInt($(this).find('total_mem').text()));
     changeClass( 'tmem_pb'+fname,100 - parseInt($(this).find('memfree_pc').text()));
     changeClass( 'smem_pb'+fname,100 - parseInt($(this).find('swapfree_pc').text()));
     $("#gs_pb"+fname).attr('aria-valuenow',($(this).find('live_games').text()));
     $("#gs_pb"+fname).attr('aria-valuemax',($(this).find('total_games').text()));
     var gs_width = ($(this).find('live_games').text() / $(this).find('total_games').text()*100)
     var pc = $(this).find('players_pc').text();
     var ts = $(this).find('total_slots').text();
     if(isNaN(pc))  {
		 // drop to zero
		 pc =0;
		 ts =0 
		 	 }
     changeClass('gs_pb'+fname,gs_width);
     changeClass('ud_pb'+fname,($(this).find('quota_pc').text()));
     $("#gs_pb"+fname).css('width',gs_width+'%');
     $("#op_pbs"+fname).html($(this).find('total_players').text()+'/'+ts);
     $("#op_pb"+fname).attr('aria-valuenow',($(this).find('total_players').text()));
     $("#op_pb"+fname).attr('aria-valuemax',($(this).find('total_slots').text()));
     $("#op_pb"+fname).css('width',pc+'%');
     console.log (fname +' '+$(this).find('total_slots').text());
    }); 
$("div").removeClass("active");
$("div").removeClass("progress-striped");
   var xmlDoc = xml;
    //console.log(bfname);  
    y=0;
    totalplayers=0; 
    totgames=0;
    activegames=0;   
    players = 0 ;
$(xml).find('Servers').children('game_server').each(function(){
	 var fname = $(this).find('name').text(); //element name important
	 var mplayers = $(this).find('maxplayers').text();
	 var bname = $(this).find('fname').text();
	 var sid = fname+" ("+$(this).find('ip').text()+")";
	 var dm =  $(this).find('defaultmap').text();
	 var us = $(this).find('update_msg').text();
	 var sv = $(this).find('version').text();
	 var udi = $(this).find('uds').text();
     var mem = $(this).find('mem').text();
     $("#mem"+fname).html($(this).find('mem').text());
	 $("#cpu"+fname).html($(this).find('cpu').text());
	 //alert (udi);
	$("#dm"+fname).html( $(this).find('defaultmap').text());
	$("#disk_used"+fname).html( $(this).find('size').text());
	 $("#sp"+fname).html($(this).find('server_pass').text());
	 $("#rp"+fname).html( $(this).find('rcon_pass').text());
	 $("#mp"+fname).html($(this).find('maxplayers').text());
	 $("#gp"+fname).html($(this).find('game_port').text());
	 $("#sp1"+fname).html( $(this).find('source_port').text());
	 $("#cp"+fname).html($(this).find('client_port').text());
	 $("#sid"+fname).html(sid);
	 $("#us"+fname).html($(this).find('update_msg').text());
	 $("#sv"+fname).html($(this).find('version').text());
	 $("#lg"+fname).attr("src",$(this).find('logo').text());
	 $("#gdate"+fname).html($(this).find('starttime').text()); //front page
	 $("#plogo"+fname).attr("src",$(this).find('logo').text()); //front page
	 $("#status"+fname).attr("src","img/offline1.png"); // set to not sure
	 $('#status'+fname).prop('title', 'Update Required');
	 var rt = $(this).find('rt').text();
	 var players = $(this).find('players').text();
	 var tp = players+"/"+mplayers;
	 //console.log(fname+' '+players+'/'+mplayers);
	 //console.log (' tp set to '+tp);
	 $("#gol"+fname).html(tp);
	 $("#pol1"+fname).html(tp);
	 var online = $(this).find('online').text();
	 if (online === "Online") {
	  $('#status'+fname).prop('title', 'Online');	 
	  $("#game"+fname).show(); //show game panel
	  $('#'+fname+'response').html(fname+' has started') ; 
	  $('#'+fname+'qbutton').show();
      $('#'+fname+'sbutton').hide();
      $('#'+fname+'cbutton').show();
      $('#'+fname+'rbutton').show();
      $('#'+fname+'vbutton').hide();
      $('#'+fname+'ubutton').hide();
      $('#'+fname+'bbutton').hide();
      $('#'+fname+'dbutton').hide();
      $('#'+fname+'response').delay(5000).fadeOut('slow');
	  $("#status"+fname).attr("src","img/online.png"); // set to online
	  //$('#'+fname+'qbutton').removeClass('btn-primary').addClass('btn-danger');
	  if (udi ==1) {
			// update req
			$("#status"+fname).attr("src","img/offline1.png"); // set to offline
			$('#status'+fname).prop('title', 'Requires Update');
			}
		 var cmap = $(this).find('currentmap').text();
	     var players = $(this).find('players').text();
	     var bots = $(this).find('bots').text();
	     oplayers = players - bots;
	     
	     var hn = $(this).find('host_name').text();
	     var tp = players+"/"+mplayers;
	     $("#po"+fname).html(oplayers+'/'+bots);
	     $("#to"+fname).html(rt);
	     $("#sn"+fname).html(hn);
	     $("#cm"+fname).html(cmap);
	     $("#pol1"+fname).html(tp);
	     $("#gol"+fname).html(tp);
	     $("#cmap"+fname).html(cmap); //front page
	    
	  
	     var sid = hn+" ("+$(this).find('ip').text()+")";
	     $("#sid"+fname).html(sid);
	     $("#padd"+fname).html(sid); // front page
	     //$('#op1'+fname).off(click); //front page
	     totgames = parseInt(totgames)+1;
	     totalplayers = parseInt(totalplayers,10) + parseInt(players,10);
	     var start = $(this).find('starttime').text();
	     $("#pbody"+fname).empty(); // clear player table rows
	 if (players >0 ){
		 //console.log(fname);
		 //btn-primary
		 //$('#'+fname+'qbutton').removeClass('btn-primary').addClass('btn-danger');
		    //$('#'+fname+'qbutton').;  
		    $('#op1'+fname).css('cursor','pointer');
		    $('#op1'+fname).off().on('click',function() 
		    {$("#ops"+fname).slideToggle("fast");});
			activegames=parseInt(activegames)+1;
		   
	        var corpName = $(this).find('pname').text();
            var result = corpName.split('|');
            var corpName = $(this).find('pscore').text();
            var score = corpName.split('|');
            var corpName = $(this).find('ponline').text();
            var time = corpName.split('|');
            var tp = '<span style="color:green;font-weight: bold;">'+players+"/"+mplayers+'</span>';
            $("#gol"+fname).html(tp);
			//console.log("cn "+corpName);
			$.each(result, function (index, value) {
				if (time[index]===""){
					// this can happen .. ignore it
				}
				else if ( typeof time[index] === "undefined") {
					// throw away tat
					//console.log("Player "+value+" Score "+ score[index]+" Time "+time[index]);
				}
				else {
					//console.log("Player "+value+" Score "+ score[index]+" Time "+time[index]);
					// here process the playerlist
					//score = score[index].val();
					//console.log(score[index]);
					var pscore = parseInt(score[index]);
					//console.log("pscore = "+pscore);
					if (pscore < 0) { 
						 pscore= "&nbsp;&nbsp;&nbsp;"+pscore;
						} 
					else
					if (pscore < 10) {
						 pscore= "&nbsp;&nbsp;&nbsp;&nbsp;"+pscore;
						 } 
					else if (pscore < 100) { 
						pscore= "&nbsp;&nbsp;"+pscore;
						}
						
					newRowContent='<tr><td><i style="color:green;">'+value+'</i></td><td align="left"><span>'+pscore+'</span></td><td>&nbsp;&nbsp;&nbsp;'+time[index]+'</td></tr>'; 
					$("#pbody"+fname).append(newRowContent);
					
					//console.log(newRowContent);
	}
    });
           
 
 }
 else {
	 //console.log("no one is playing on "+fname+" Current Map "+cmap+ " started at "+start);
	 //here make sure playerlist is empty & update times etc
	 $("#pol1"+fname).html("");
	 
	 $("#ops"+fname).slideUp(); //close player panel
	 //$('#'+fname+'qbutton').removeClass('btn-danger').addClass('btn-primary');
	 //$('#op1'+fname).click(false);
	 $('#op1'+fname).off('click');
	 $('#op1'+fname).css('cursor','default');
	
 }
 
 y=y+1; 
	
 }
 else {
	 //console.log(fname+" is off line");
	 // todo add screen session Id to xml 
	 $("#pol1"+fname).html("");
	 st = "";
	 if (st === "" ) {
		$("#game"+fname).hide(); //hide game panel
		$('#'+fname+'response').html(fname+' has stopped') ; 
		$("#status"+fname).attr("src","img/offline.png"); // set to offline
		$('#status'+fname).prop('title', 'Off Line');
		if (udi ==1) {
			// update req
			$("#status"+fname).attr("src","img/offline1.png"); // set to offline
			$('#status'+fname).prop('title', 'Requires Update');
			}
		
		$('#'+fname+'response').delay(5000).fadeOut('slow');
		$('#'+fname+'qbutton').hide();
		$('#'+fname+'sbutton').show();
		$('#'+fname+'cbutton').hide();
		$('#'+fname+'rbutton').hide();
		$('#'+fname+'vbutton').hide();
		$('#'+fname+'ubutton').hide();
		$('#'+fname+'bbutton').show();
		$('#'+fname+'dbutton').show();
		$('#'+fname+'ebutton').show();
	}
	else {
		// mid state screen open but no response
		$("#status"+fname).attr("src","img/offline1.png"); // set to mid state
		//$('#'+fname+'response').delay(5000).fadeOut('slow');
	}
	 $("#to"+fname).html('N/A');
	 $("#cm"+fname).html('N/A');
	 $("#po"+fname).html('N/A');
	 $('#mem'+fname).html('N/A');
     $('#cpu'+fname).html('N/A');
	
     // clear current map/players etc
 }
  //add outer data 
  $("#gsr"+bname).html(totgames);
  $("#asr"+bname).html(activegames);
  $("#tp"+bname).html(totalplayers);
  
    });
    // end foreach
  
  },
  complete:function(xml){
     setTimeout(fetchservers,4000);
  }
 });
}

$("#aboutcheck").click(function() {
alert("not cooking yet");
$('#aboutcheck').blur();
});

function changeClass(id,rate) {
	
if ( $( "#"+id ).length ) {	
     var classList = document.getElementById(id).className.split(/\s+/);
     //console.log(classList+' '+rate);
     //$('#').width($('#object').parent().width());
     for (var i = 0; i < classList.length; i++) {
		 
    if (classList[i] !== 'progress-bar') {
          $('#'+id).removeClass(classList[i]);
	  }
        switch (true)
			{
	   // do add
	   case rate <=25 :
	   break;
	   case rate <=50 :
	   //console.log('less than 33');
	   $('#'+id).addClass('progress-bar-success');
	   break;
	    case rate <=75 :
	   //console.log('less than 66');
	   $('#'+id).addClass('progress-bar-warning');
	    break;
	    case rate >75 :
	    $('#'+id).addClass('progress-bar-danger');
	    break;
		}
	
		}
	} 
}
