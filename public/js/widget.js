var currScript,jQEngager,peer_name,roomIdw,agentId,iframeId,offlineEmail,offlinePage,visitor_name,createRoom,configType,lsvdatetime,lsvduration,disableVideo,disableAudio,disable,dataHosts,noOnlineMembers,currVersion=15,isOnline=!1,popupInstance=null,openTab=!1,autoReconnectInterval=5000;if(currScript=document.currentScript||function(){var a=document.getElementById("newdev-embed-script");return a}(),null==currScript||currScript==null){var scripts=document.getElementsByTagName("script"),index=scripts.length-1;currScript=scripts[index]}if("undefined"==typeof socket)var socket;var lsRepUrl=currScript.getAttribute("data-source_path");if(null==lsRepUrl||lsRepUrl==null)for(var scripts=document.getElementsByTagName("script"),i=0,l=scripts.length;i<l;i++)if(scripts[i].getAttribute("data-source_path")){currScript=scripts[i],lsRepUrl=currScript.getAttribute("data-source_path");break}function makeid(a){let b="";const c="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789".length;for(let d=0;d<a;)b+="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789".charAt(Math.floor(Math.random()*c)),d+=1;return b}var initVideoNewDev=function(){var a,b=currScript.getAttribute("data-button_css")?currScript.getAttribute("data-button_css"):"button_gray.css",c=currScript.getAttribute("data-message")?currScript.getAttribute("data-message"):"Start Video Chat";peer_name=currScript.getAttribute("data-host_name")?currScript.getAttribute("data-host_name"):"",visitor_name=currScript.getAttribute("data-visitor_name")?currScript.getAttribute("data-visitor_name"):"",agentId=currScript.getAttribute("data-tenant")?currScript.getAttribute("data-tenant"):"",roomIdw=currScript.getAttribute("data-room_id")?currScript.getAttribute("data-room_id"):agentId,roomIdw=roomIdw?roomIdw:makeid(10),iframeId=currScript.getAttribute("data-iframe_id")?currScript.getAttribute("data-iframe_id"):"",offlineEmail=currScript.getAttribute("data-offline_email")?currScript.getAttribute("data-offline_email"):"",offlinePage=currScript.getAttribute("data-offline_page")?currScript.getAttribute("data-offline_page"):"",openTab=!!currScript.getAttribute("data-intab")&&currScript.getAttribute("data-intab"),configType=!!currScript.getAttribute("data-config")&&currScript.getAttribute("data-config"),lsvdatetime=!!currScript.getAttribute("data-datetime")&&currScript.getAttribute("data-datetime"),lsvduration=!!currScript.getAttribute("data-duration")&&currScript.getAttribute("data-duration"),disableVideo=!!currScript.getAttribute("data-disablevideo"),disableAudio=!!currScript.getAttribute("data-disableaudio"),disable=!!currScript.getAttribute("data-disable"),dataHosts=!!currScript.getAttribute("data-hosts")&&currScript.getAttribute("data-hosts"),createRoom=currScript.getAttribute("data-cr")?currScript.getAttribute("data-cr"):"",noOnlineMembers=currScript.getAttribute("data-noonline")?currScript.getAttribute("data-noonline"):"There are no online staff members";var d=function(b){if(b&&(roomIdw=jQEngager(b.target).data("room"),b.stopPropagation(),jQEngager("#widget_tooltip_container").hide()),popupInstance&&!popupInstance.closed)popupInstance.focus();else{var c=screen.width-100,d=screen.height-100,e=screen.width/2-c/2,f=screen.height/2-d/2,g={};g.request=!0,peer_name&&(g.agentName=peer_name),visitor_name&&(g.visitorName=visitor_name),agentId&&(g.agentId=agentId),configType&&(g.config=configType),lsvdatetime&&(g.datetime=lsvdatetime),lsvduration&&(g.duration=lsvduration),disableVideo&&(g.disableVideo=!0),disableAudio&&(g.disableAudio=!0),disable&&(g.disable=!0);var h=window.btoa(unescape(encodeURIComponent(JSON.stringify(g))));if(openTab)var i="";else i="width="+c+", height="+d+", left="+e+", top="+f+", location=no, menubar=no, resizable=yes, scrollbars=no, status=no, titlebar=no, toolbar = no";if(a=lsRepUrl+roomIdw+"?p="+h,iframeId)jQEngager("#"+iframeId).attr("src",a);else{popupInstance=window.open("","popup_instance",i);try{"about:blank"===popupInstance.location.href&&(popupInstance.location.href=a)}catch(a){}popupInstance.focus()}}},e=function(){jQEngager.ajax({url:lsRepUrl+"config/config.json?v="+currVersion,type:"GET",timeout:5e3,dataType:"json",beforeSend:function(a){a&&a.overrideMimeType&&a.overrideMimeType("application/j-son;charset=UTF-8")},success:function(a){svConfigs=a,f()}})},f=function(){jQEngager.ajax({type:"POST",url:lsRepUrl+"server/script.php",data:{type:"getapikey"}}).done(function(a){a&&loadScript(lsRepUrl+"/socket.io/socket.io.js",g)}).fail(function(){})},g=function(){socket=io(lsRepUrl),socket.request=function(a,b={}){return new Promise((c,d)=>{socket.emit(a,b,a=>{a.error?d(a.error):c(a)})})},socket.on("connect",function(){if(createRoom){let a=createRoom;socket.request("createRoom",{room_id:a}).then(function(a){let b={room_id:a.id,peer_info:{peer_admin:1,peer_name:createRoom}};socket.request("join",b).then(async function(){}.bind(this)).catch(a=>{console.error("Join error:",a)})}.bind(this)).catch(a=>{console.error("Create room error:",a)}),socket.on("requestSession",function(a){$("#requestModal").modal("show"),$("#request_session").text($("#request_session").text().replace("{{visitor}}",a.peer_name));new URL(window.location.href);let b={};b.admin=1,b.config=a.room_id,agentUrl=lsRepUrl+a.room_id+"?p="+window.btoa(unescape(encodeURIComponent(JSON.stringify(b)))),$(document).on("click","#openUrl",function(){window.open(agentUrl,"_blank"),$("#requestModal").modal("hide"),socket.request("exitRoom").catch(a=>{console.error("Create room error:",a)})}),$(document).on("click","#closeUrl",function(){$("#requestModal").modal("hide"),socket.request("exitRoomAll").catch(a=>{console.error("Create room error:",a)})})}.bind(this))}if(dataHosts)jQEngager("#widget_but_span").hide();else{var a=function(){let b=agentId;socket.request("checkPresence",{room_id:b}).then(function(a){isOnline=!!a,isOnline&&""==agentId&&(agentId=roomIdw=a),h()}.bind(this)).catch(a=>{isOnline=!1,h(),console.error("Create room error:",a)}),setTimeout(function(){a()},3e3)};a()}}.bind(this)),socket.on("disconnect",function(){isOnline=!1,h()}.bind(this)),socket.on("producerClosed",function(){isOnline=!1,h()}.bind(this)),socket.on("exitRoom",function(){isOnline=!1,h()}.bind(this));var a=jQEngager("<link>",{rel:"stylesheet",type:"text/css",href:lsRepUrl+"css/"+b+"?v="+currVersion});a.appendTo("head"),$container=jQEngager("#nd-widget-container"),i($container)},h=function(){isOnline?jQEngager("#widget_but_span").attr("class","online-bnt"):jQEngager("#widget_but_span").attr("class","offline-bnt")},i=function(a){jQEngager.get(lsRepUrl+"pages/button.html",function(b){a.append(b),jQEngager("#newdev_widget_msg").html(c),jQEngager("#widget-but").on("click",function(){if(dataHosts){var a=!1;jQEngager("#widget_tooltip_container").show();var b=dataHosts.split(","),c=function(){jQEngager("#widget_tooltip_container").empty(),jQEngager("#widget_tooltip_container").addClass("bottom"),jQEngager.each(b,function(b,c){if(c){var e=c.split("|");let b=e[0];socket.request("checkPresence",{room_id:b}).then(function(b){if(b){a=!0;var c=jQEngager("#lsv-product-template").clone();jQEngager(".lsv-product-name",c).html(e[1]),jQEngager(".lsv-product-name",c).attr("id","href"+b),jQEngager(".lsv-product-name",c).attr("data-room",b),jQEngager("#widget_tooltip_container").append(c.html()),document.getElementById("href"+b).addEventListener("click",d,!1)}}.bind(this)).catch(a=>{console.error("Create room error:",a)})}}),setTimeout(function(){if(!a){var b=jQEngager("#lsv-product-template").clone();jQEngager(".lsv-product-name",b).html(noOnlineMembers),jQEngager("#widget_tooltip_container").append(b.html()),setTimeout(function(){jQEngager("#widget_tooltip_container").empty()},4e3)}},2e3)};c()}else isOnline?d():offlineEmail?(jQEngager("#widget_tooltip_container").show(),jQEngager("#widget_tooltip_container").on("click",function(a){jQEngager("#widget_tooltip_container").hide(),a.stopPropagation()}),jQEngager("#offline_message").on("click",function(a){a.stopPropagation()}),jQEngager("#sendEmail").on("click",function(){jQEngager("#widget_tooltip_container").hide();var a=jQEngager("#offline_message").val();document.location.href="mailto:"+offlineEmail+"?subject=Offline request&body="+a})):offlinePage&&(-1===offlinePage.indexOf("http")&&(offlinePage="http://"+offlinePage),window.location.href=offlinePage)})})};e()};function loadScript(a,b){var c=document.createElement("script");c.type="text/javascript",c.readyState?c.onreadystatechange=function(){("loaded"==c.readyState||"complete"==c.readyState)&&(c.onreadystatechange=null,b&&b())}:(c.onload=function(){b&&b()},c.onerror=function(){setTimeout(function(){loadScript(a,b)},autoReconnectInterval)}),c.src=a+"?v="+currVersion,document.getElementsByTagName("head")[0].appendChild(c)}var ms=Date.now();"undefined"==typeof jQuery||11>parseInt(jQuery.fn.jquery.split(".")[1])?loadScript("https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js",function(){jQEngager=jQuery.noConflict(!0),jQEngager.get(lsRepUrl+"pages/version.txt?v="+ms,function(a){currVersion=a,jQEngager(document).ready(new initVideoNewDev)})}):(jQEngager=jQuery,jQEngager.get(lsRepUrl+"pages/version.txt?v="+ms,function(a){currVersion=a,jQEngager(document).ready(new initVideoNewDev)}));