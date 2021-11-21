  
jQuery(document).ready(function(){
    
    updateClock();
    setInterval('updateClock()', 1000);
    //jQuery('.tooltips').tooltip();

    //popovers
    //jQuery('.popovers').popover();
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
    //console.log(currentTime.getTime());  
     }

function online(url,timeout,hide){
        console.log('Welcome to online using '+url);
	 $.ajax({
     url: url,
   type: 'post',
  dataType: "json" ,
  success: function(xml,status){
		  var data1 = xml;
	
for (var i in data1) {
	//console.log(i);
	var fname = i; // got the base server
	//$('#demo').append('<p>'+fname+'</p>');
	if( i =='general' ) {
		var general = data1[i];
		
		for (g in general) {
			//console.log (general[g]+' '+g );
			//$('#demo').append('<p>'+g+' '+general[g]+'</p>');

		} 
		return;
	}	
   for (var j in data1[i]) {
	// we have the individal server
	console.log('Processing '+j);
	 if (typeof serverlength === 0) {
						   console.log('server not set');
						   			return;
						   			
								}   
	var server = data1[i][j]; // got server id
	
	var server_id = j;	
	if (server.running == 1 ) {
		//return;
	//}
	// $('#pl'+server_id).empty();
	 var playern = server.Players;
	 //console.log(playern);
	  $('#pl'+server_id).html(playern); 
	 //alert(server_id);
     	console.log(server.server_name);
	                  $("#"+server_id).show();
	                   var start_date = timeConverter(parseFloat(server.starttime));
					//console.log(start_date);
					   var logo  =server.url+':'+server.bport+'/'+server.logo;
					  //console.log('server.Players = '+server.Players );
					   if (typeof server.Players === "undefined") {
						   console.log('Players not set '+server.server_name);
						   			//return true;
						   			server.Players = 0;
								}   
					    $("#img"+server_id).attr("src",logo);
						$('#cmap'+server_id).html(server.Map);
						$('#host'+server_id).html(server.server_name);
						$('#gdate'+server_id).html(start_date);
						$('#gol'+server_id).html(server.Players+'/'+server.max_players);
						 
                        if (server.Players ==0 ) {
							//console.log ('should be nowt '+server.Players);
							$("#ops"+server_id).slideUp();
							$('#op1'+server_id).css('cursor','default');  
							$('#gol'+server_id).removeClass('p_count'); 
							
						}
						else if (server.Players >0) {
							
											
								if (typeof server.players === "undefined") {
									console.log('players array not set '+server.server_name);
									return;
								}   
								 $('#op1'+server_id).css('cursor','pointer');
								 $('#gol'+server_id).addClass('p_count');	
								 $("#pbody"+server_id).empty();
								 var players = server.players;
								 var players = players.sort((b, a) => (a.Frags > b.Frags) ? 1 : -1)
								//console.log(players);
								 for (p in players) {
										newRowContent='<tr style="font-size:14px;"><td style="width:60% !important;"><i class="p_name">'+players[p].Name+'</i></td><td style="text-align:right;width:15%; !important;padding-right:14%" class="p_score">'+players[p].Frags+'</td><td style=text-align:right;padding-right:3%;width:20%" class="p_time">'+players[p].TimeF+'</td></tr>'; 
										$("#pbody"+server_id).append(newRowContent);
										}
							
										
						}
	
                  
   }
   
   else {
			$('#'+server_id).hide(); // hide the server
	}					
}	
}
	    
  },
          fail: function() {
            //Something went wrong. Inform user/try again as appropriate
            alert('Failed');
            //setTimeout('Update()', 2000);
        },
  complete:function(data,data1){
	 
    // console.log('hide loading '); 
	setTimeout(online(url),timeout);
	if (hide == 1 ) {
		$('#loading').hide();
		$('#game_block').show();
	}
  }
   });
}
 
 // add the static display function
 function  game_detail(url) {
	 //console.log(url);
	 
	 	 $.ajax({
     url: url,
   type: 'post',
  dataType: "json" ,
  success: function(xml,status){
		  var data1 = xml;
	
for (var i in data1) {
	//console.log(i);
	var fname = i; // got the base server
	//$('#demo').append('<p>'+fname+'</p>');
	if( i =='general' ) {
		var general = data1[i];
		
		for (g in general) {
			//console.log (general[g]+' '+g );
			//$('#demo').append('<p>'+g+' '+general[g]+'</p>');

		} 
		return;
	}	
   for (var j in data1[i]) {
	// we have the individal server
	//console.log('Processing '+j);
	var ud=0;
	 if (typeof serverlength === 0) {
						   console.log('server not set');
						   			return;
						   			
								}   
	var server = data1[i][j]; // got server id
	
	var server_id = j;
	//console.log("Found "+server_id);
	var today= new Date();
	var date = new Date(server.starttime*1000);
	if (server.buildid !== server.rbuildid) {
		console.log (server_id+" needs update");
		ud=1;
		if (server.running == 1) {
			var running=1;
		}
	} 	
	if (server.running == 1 && ud == 0  ) {
		//return;
		console.log( server_id+" running");
		$('#status'+server_id).attr("src","img/online.png");
		var running =1;
		
	}
	else if  (server.running == 1 && ud == 1  ) {
		console.log(server_id+" running - ud");
		$('#status'+server_id).attr("src","img/offline1.png");
		var running=1;
	}
	else {
		//console.log(server_id+' not running');
		server.Players=0;
		server.Bots=0;
		$('#status'+server_id).attr("src","img/offline.png");
		if (ud == 1) {
			$('#status'+server_id).attr("src","img/offline1.png");
		}
		var today=0;
		var date =0;
		var running=0;
	}
	//<img id="lgcssserver" style="width:5%;" src="img/240.ico">
	$('#lg'+server_id).attr("src",server.logo);
	$('#na'+server_id).text(server.server_name);
	$('#sn'+server_id).text(server.HostName);
	$('#sp'+server_id).text(server.server_password);
	$('#rp'+server_id).text(server.rcon_password);
	$('#dm'+server_id).text(server.default_map);
	$('#mp'+server_id).text(server.max_players);
	$('#gp'+server_id).text(server.port);
	$('#sp1'+server_id).text(server.client_port);
	$('#cp'+server_id).text(server.source_port);
	$('#sv'+server_id).text(server.buildid);
	$('#disk_used'+server_id).text(server.size);
	$('#us'+server_id).text(timeConverter(server.server_update));
	
	if (running == 1) {
		if (typeof server.Bots == 'undefined') {
			return true
		}
		r = timeBetween(today,date);
		$('#to'+server_id).text(r.day+' days '+r.hour+' hours '+r.minute+' minutes '+r.second+' seconds');
		$('#cm'+server_id).text(server.Map);
		$('#po'+server_id).text((server.Players-server.Bots)+'/'+server.Bots);
		$('#cpu'+server_id).text(server.cpu);
		$('#mem'+server_id).text(server.mem);
		$('#pol1'+server_id).text(server.Players+'/'+server.max_players);
		// data done  do buttons
		$('#'+server_id+'qbutton').show(); //stop
		$('#'+server_id+'sbutton').hide(); //start
		$('#'+server_id+'cbutton').show(); //console
		$('#'+server_id+'rbutton').show(); //restart
		$('#'+server_id+'vbutton').hide();
		$('#'+server_id+'ubutton').hide();
		$('#'+server_id+'bbutton').hide(); //backup
		$('#'+server_id+'dbutton').hide(); // disable
		$('#'+server_id+'ebutton').show(); //edit
		if (server.Players !== 0) {
			// add classes
		  $('#'+server_id+'qbutton').removeClass('btn-primary').addClass('btn-danger');
		  $('#'+server_id+'rbutton').removeClass('btn-primary').addClass('btn-danger');
		  $('#'+server_id+'ebutton').removeClass('btn-primary').addClass('btn-warning');
		}
		else {
			// restore class
			$('#'+server_id+'qbutton').removeClass('btn-danger').addClass('btn-primary');
		    $('#'+server_id+'rbutton').removeClass('btn-danger').addClass('btn-primary');
		    $('#'+server_id+'ebutton').removeClass('btn-warning').addClass('btn-primay');
		}
	}
	else {
		// off server
		$('#cm'+server_id).text('N/A');
		$('#po'+server_id).text('N/A');
		$('#cpu'+server_id).text('N/A');
		$('#mem'+server_id).text('N/A');
		$('#to'+server_id).text('N/A');
		//$('#pol1'+server_id).text('0/0');
		$('#'+server_id+'qbutton').hide(); //stop
		$('#'+server_id+'sbutton').show(); //start
		$('#'+server_id+'cbutton').hide(); //console
		$('#'+server_id+'rbutton').hide(); //restart
		$('#'+server_id+'vbutton').hide();
		$('#'+server_id+'ubutton').hide();
		$('#'+server_id+'bbutton').show(); //backup
		$('#'+server_id+'dbutton').show(); // disable
		$('#'+server_id+'ebutton').show(); //edit
		$('#'+server_id+'ebutton').removeClass('btn-warning').addClass('btn-primary');
	}
	
}
}    
  },
          fail: function() {
            //Something went wrong. Inform user/try again as appropriate
            alert('Failed');
            //setTimeout('Update()', 2000);
        },
  complete:function(data,data1){
	 $('#games').show();
	 $('#loading').hide();
    // console.log('hide loading '); 
	setTimeout(game_detail(url),5000);
	
	}
  //}
   });
 }
 function server_info(url) {
	 //get player functions
	 console.log(url);
	  $.ajax({ 
        type: 'GET', 
        url: url, 
        dataType: "json", 
        success: function (data) {
			console.log('got data');
             
           
        },
        complete:function(data){
			 console.log('jog done'); 
		}
    });
 }
 
 function base_servers(url) {
	 // bring back base_server detail
	  $.ajax({ 
        type: 'GET', 
        url: url, 
        dataType: "json", 
        success: function (data) {
			var server_id = data.server_id; 
            //console.log(server_id);
            $('#boot'+server_id).text(data.boot_time);
            $('#cpu_model'+server_id).text(data.model_name);
            $('#cpu_processors'+server_id).text(data.processors);
            $('#cpu_cores'+server_id).text(data.cpu_cores);
            $('#cpu_speed'+server_id).text(data.cpu_MHz);
            $('#load'+server_id).text(data.load);
            $('#cpu_cache'+server_id).text(data.cache_size);
            $('#ip'+server_id).text(data.ips);
            $('#reboot'+server_id).text(data.reboot);
            $('#boot_filesystem'+server_id).text(data.root_filesystem);
            $('#boot_mount'+server_id).text(data.root_mount);
            $('#boot_size'+server_id).text(data.root_size);
            $('#boot_used'+server_id).text(data.root_used+' ('+data.root_pc+')');
            $('#boot_free'+server_id).text(data.root_free);
            $('#memtotal'+server_id).text(data.MemTotal);
            $('#memfree'+server_id).text(data.MemFree);
            $('#memcached'+server_id).text(data.Cached);
            $('#memactive'+server_id).text(data.MemAvailable);
            $('#swaptotal'+server_id).text(data.SwapTotal);
            $('#swapfree'+server_id).text(data.SwapFree); 
            // mem graphs
            var tmem =Math.round(100-(parseInt(data.MemFree_raw)/parseInt(data.MemTotal_raw))*100)
            //mem graph
            $('#tmem_pb'+server_id).css('width',tmem+'%');
            $('#tmem_pbs'+server_id).width($('#tmem_pb'+server_id).parent().width());
            $("#tmem_pbs"+server_id).html('Free ('+data.MemFree+')');
            changeClass( 'tmem_pb'+server_id,tmem);
            //swap graph
            var smem = Math.round(100-(parseInt(data.SwapFree_raw)/parseInt(data.SwapTotal_raw))*100);
            $('#smem_pb'+server_id).css('width',smem+'%');
            $("#smem_pbs"+server_id).html('Free ('+data.SwapFree+')');
            $('#smem_pbs'+server_id).width($('#smem_pb'+server_id).parent().width());
            changeClass( 'smem_pb'+server_id,smem);
            //end mem graphs
            $('#hname'+server_id).text(data.host);
            $('#distro'+server_id).text(data.os);
            $('#kernel'+server_id).text(data.k_ver);
            $('#process'+server_id).text(data.process);
            $('#php'+server_id).text(data.php);
            $('#glibc'+server_id).text(data.glibc);
            $('#screen'+server_id).text(data.screen);
            $('#apache'+server_id).text(data.apache);
            $('#mysql'+server_id).text(data.mysql);
            $('#curl'+server_id).text(data.curl);
            $('#nginx'+server_id).text(data.nginx);
            $('#quota'+server_id).text(data.quotav);
            $('#postfix'+server_id).text(data.postfix);
             var x =  parseFloat(data.total_size_raw.toFixed(2))/1000000;
             var quota_pc = x* (100/parseFloat(data.quota));
             // game graph
            $("#gs_pb"+server_id).attr('aria-valuenow',data.live_servers);
            $("#gs_pbs"+server_id).text(data.live_servers+'/'+data.total_servers);
            $('#gs_pbs'+server_id).width($('#gs_pb'+server_id).parent().width());
            var gs_width = parseInt(data.live_servers) / parseInt(data.total_servers)*100;
            $('#gs_pb'+server_id).css('width',gs_width+'%');
            
            changeClass('gs_pb'+server_id,gs_width);
            // disk used graph
            $("#ud_pb"+server_id).attr('aria-valuemax',data.total_servers);
            $("#ud_pbs"+server_id).text(data.total_size);
            $('#ud_pbs'+server_id).width($('#ud_pb'+server_id).parent().width());
            $("#ud_pb"+server_id).css('width',quota_pc+'%');
             changeClass('ud_pb'+server_id,quota_pc);
            // mem used graph
            $('#mem_pbs'+server_id).width($('#mem_pb'+server_id).parent().width());
            $("#mem_pbs"+server_id).text(data.total_mem+'%');
            $("#mem_pb"+server_id).css('width',data.total_mem+'%');
              changeClass('mem_pb'+server_id,parseInt(data.total_mem));
            //cpu graph
             $('#cpu_pbs'+server_id).width($('#cpu_pb'+server_id).parent().width());
             $("#cpu_pbs"+server_id).text(data.total_cpu+'%');
             $("#cpu_pb"+server_id).css('width',data.total_cpu+'%');
              changeClass('cpu_pb'+server_id,parseInt(data.total_cpu));
             // slots graph
             $('#op_pbs'+server_id).width($('#op_pb'+server_id).parent().width());
             $("#op_pbs"+server_id).text(data.total_players+'/'+data.total_bots+'/'+data.total_slots);
             var player_pc = data.used_slots/data.total_slots*100; 
             $("#op_pb"+server_id).css('width',player_pc+'%');
             changeClass('op_pb'+server_id,Math.round(player_pc));
             
           
        },
        complete:function(data,data1){
			    setTimeout(base_servers(url),3000);
				$('#games').show();
				$('#loading').hide();
				$("div").removeClass("active");
				$("div").removeClass("progress-striped");
		}
    });

 }
 
 /* 
  * General functions
  * 
  */
 
function timeConverter(UNIX_timestamp){
  var a = new Date(UNIX_timestamp * 1000);
  var months = ['January','Febuary','March','April','May','June','July','August','September','October','November','December'];
  var weekday = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
  var year = a.getFullYear();
  var month = months[a.getMonth()];
  var day = weekday[a.getDay()];
  var date = a.getDate();
  var hour = a.getHours();
  var timeOfDay = ( hour < 12 ) ? "am" : "pm"; 
  currentHours = ( hour > 12 ) ? hour - 12 : hour;
     // Convert an hours component of "0" to "12"
    
  //hour = ( currentHours == 0 ) ? 12 : currentHours;
 
  //var date = dateOrdinal(date);
  var d = a.getDate();
	var d1 =a.getHours();
console.log(currentHours);
var d = ('0'+d).slice(-2);
	var m = a.getMonth()+1;
var m = ('0'+m).slice(-2);

var hour =('0'+hour).slice(-2);
        //m += 1;  // JavaScript months are 0-11
	var y = a.getFullYear();
  var min = ('0'+a.getMinutes()).slice(-2);
  var sec = a.getSeconds();

  var time = d+ '-' + m + '-' + y + ' ' + hour + ':' + min ;
  return time;
}

function dateOrdinal(dom) {
    if (dom == 31 || dom == 21 || dom == 1) return dom + "st";
    else if (dom == 22 || dom == 2) return dom + "nd";
    else if (dom == 23 || dom == 3) return dom + "rd";
    else return dom + "th";
}

function timeBetween (date1,date2) {
	// get time between
		var d = Math.abs(date1 - date2) / 1000;                 // delta
var r = {};                                                                // result
var s = {                                                                  // structure
    year: 31536000,
    month: 2592000,
    week: 604800, // uncomment row to ignore
    day: 86400,   // feel free to add your own row
    hour: 3600,
    minute: 60,
    second: 1
};

Object.keys(s).forEach(function(key){
    r[key] = Math.floor(d / s[key]);
    d -= r[key] * s[key];
});
return r;
}

function changeClass(id,rate) {
	
if ( $( "#"+id ).length ) {	
     var classList = document.getElementById(id).className.split(/\s+/);
     //console.log(classList+' '+rate);
     //$('#').width($('#object').parent().width());
     for (var i = 0; i < classList.length; i++) {
		 
    if (classList[i] !== 'progress-bar') {
          $('#'+id).removeClass(classList[i]);
	  }
	 
	  color = percentToRGB(rate);
	  $('#'+id).css('background-color',color); 
		}
	} 
}

function percentToRGB(percent) {
    if (percent >= 100) {
        percent = 99
    }
    var r, g, b;

    if (percent < 50) {
        // green to yellow
        r = Math.floor(255 * (percent / 50));
        g = 255;

    } else {
        // yellow to red
        r = 255;
        g = Math.floor(255 * ((50 - percent % 50) / 50));
    }
    b = 0;

    return "rgb(" + r + "," + g + "," + b + ")";
}
