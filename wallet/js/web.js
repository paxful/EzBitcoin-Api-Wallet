



function jsfunct_Report(strURL){
	//this function submits report requests
	//$.ajax({ type: "POST", async: false, url: strURL, data: dataString,	success: function(result) {
	$.post(strURL , function(data){
		
			if (data == 'done') { //success
				
				jsfunct_Alert('Done! Thank you');
			}
	});
}




function jsfunct_UpdatedItem(intItemID,strClose){

	jsfunct_Alert('Item Updated. Refresh to see changes.');
	
	//give user feedback
	soundManager.play('lvlup','/sounds/itemuse.mp3'); //play sound
	
	if(strClose){
		//close window
		toggle_visibility(strClose);
		//$("#window_edit").hide();
	}
	
}



// file options for admins
function jsfunct_MainPic(intMediaId, intMediatype, strURL){
	$.post(strURL ,
		function(data){
			if (data == 'done') {
				//give them some feedback... sound, animation ?
				soundManager.play('exp','/sounds/beep.mp3'); //play sound
			}
	});
}
function jsfunct_StickMedia(intMediaId, intMediatype, strURL){
	$.post(strURL ,
		function(data){
			if (data == 'done') {
				soundManager.play('exp','/sounds/beep.mp3'); //play sound
			}
	});
}
function jsfunct_StarMedia(intMediaId, intMediatype, strURL){
	$.post(strURL ,
		function(data){
			if (data == 'done') {
				soundManager.play('exp','/sounds/beep.mp3'); //play sound
			}
	});
}
function jsfunct_Media_Copy(intMediaId, strURL){
	$.post(strURL ,
		function(data){
			if (data == 'done') {
				jsfunct_Alert('Done! File Copied');
				toggle_visibility('window_item_copy' + intMediaId);//hide action window
			}
	});
}
function jsfunct_Media_Move(intMediaId, strURL){
	$.post(strURL ,
		function(data){
			if (data == 'done') {
				jsfunct_Alert('Done! File Moved');
				toggle_visibility('window_item_copy' + intMediaId);//hide action window
				$('#container_cell_' + intMediaId).hide(); //hide cell
			}
	});
}
	// file options for admins END




function jsfunct_OpenJoin(){
	
	jsfunct_LoadWindows('window_item', '/mods/join.php?do=login');//open iframe
	parent.soundManager.play('select','/sounds/bonemissle.mp3'); //play sound 
}


var intSearchCount = 0 ;
function jsfunct_Search(strTXT) {
	alert('txt=' + strTXT);	
	if(strTXT){ 
		
		strSearchURL = '/market.php?type=1&searchtxt=' + strTXT ;
		gotourl(strSearchURL); //redirect form
	}else{
		jsfunct_Alert('Please type something to search for'); return ;
	}
}


function jsfunct_AjaxDo(intMediaId, intMediatype, strURL){
	$.post(strURL ,
		function(data){
			if (data == 'done') {
				soundManager.play('exp','/sounds/beep.mp3'); //play sound
			}
	});
}


function jsfunct_Alert(strTxt,intDelay){
	
	if(!intDelay){intDelay=1500;}

	soundManager.play('exp','/sounds/beep.mp3'); //play sound
	document.getElementById('window_alert_txt').innerHTML = strTxt;
	$('#window_alert').fadeIn(500).delay(intDelay).fadeOut(500); //animate it

}

function jsfunct_Alert_Debug(strTxt){
	
	//soundManager.play('exp','/sounds/beep.mp3'); //play sound
	document.getElementById('window_alertdebug_txt').innerHTML = strTxt;
	$('#window_alertdebug').fadeIn(500)
}

function jsfunct_LoadIframe(strDivID,strURL) { //open url in iframe 'iFrameID'
	
	document.getElementById(strDivID).src=strURL; //change iframe to new url
}

function openInIFrame(strDivID,strURL) { //open url in iframe 'iFrameID'
	
	document.getElementById(strDivID + '_iframe').src=strURL; //change iframe to new url
	
	//if virgin click OR url are not same then toggle
	if( strPreviousIframeURL=='' || strURL==strPreviousIframeURL ){
		toggle_visibility(strDivID); //open window
		intOpenIframeCount = intOpenIframeCount + 1 ;
		}
		
	strPreviousIframeURL = strURL ;	//global 
}
	
function scrollToAnchor(aid){
    var aTag = $("a[name='"+ aid +"']");
    $('html,body').animate({scrollTop: aTag.offset().top},'slow');
}	
	
	
function toggle_visibility(id) {
    var e = document.getElementById(id);
    if (e.style.display == 'block')
        e.style.display = 'none';
		//strFrameLayerToggle = document.getElementById(id) + 'OFF' ;
    else
        e.style.display = 'block';
		//strFrameLayerToggle = document.getElementById(id) + 'ON' ;
	}	
	

var strIframeURL = '' ; //global container
function jsfunct_LoadWindows(strDivID,strURL){
	//detects if window is already open and if so closes it, or if different src then update src withoutclosing	
    
    if( $('#'+strDivID).is(':visible') ) { //detect with jquery (jquery required)
	// window is visible,
            
            if(strIframeURL!=strURL){//if the window url is new then
            
                document.getElementById(strDivID+'_iframe').src=strURL;  //change keep window open and just change iframe src
            
            }else{     
                //close div and unload iframe
                document.getElementById(strDivID).style.display = 'none' ;
                document.getElementById(strDivID+'_iframe').src=''; //change iframe to new url
            }
	}else {
	    // it's not visible so open div and load iframe
            document.getElementById(strDivID).style.display = 'block' ;
            document.getElementById(strDivID+'_iframe').src=strURL; //change iframe to new url
	}
        
        strIframeURL = strURL ; //update global container 
    
}




function jsfunct_LoginPrompt(strMSG){
	
	//call up login window
	openInIFrame_All('hud_join_iframe','hud_join', '/join.php?msg=' + strMSG);

}






function jsfunct_ParentRefresh(strURL){

	window.top.location.href = strURL; 	
	//window.top.location.href = "http://" + strURL; 	
	
}


function jsfunct_UpdatePlayCount(intMediaId){
	
	//Update db
	$.post('/ajax/ajax_do.php?do=updateplaycount&id=' + intMediaId ,
		function(data){
			if (data) {
				return data ;
			}
			//alert(data);
	});
	

}




function jsfunct_DeleteMedia(intMediaID, strRedirect, strURL){
	
	//Delete from DB, FS
	$.post(strURL ,
		function(data){
			if (data == 'done') {
				
				//give them some feedback... sound, animation ?
				//alert('caption deleted');
				$('#container_cell_' + intMediaID).hide(); //hide cell
				//$container.masonry( 'remove', 'container_cell_' + intMediaId ).masonry( 'reload' );//remove item from masonry
				//$container.remove( 'container_cell_' + intMediaId ).masonry( 'reload' );	//reload?
				soundManager.play('exp','/sounds/beep.mp3'); //play 
				jsfunct_Alert('Deleted');
				
				if(strRedirect=="item"){//item page from parent so 
					$.parent.colorbox.close(); //close current colorbox window
				}else{ 
				
					if(strRedirect){//item page raw with hud included so redirect them to url on finish
						window.top.location.href = strRedirect ;//redirect to chest page
					}
				}
	
			}
			//alert(data);
	});
	

}



function functAjax_Add2Locker( intMediaID , intMediaType , intUserID, strLockerKEY, strURL ){

	$.ajax({
		type: 'GET',
		url: strURL + '?id=' + intMediaID + '&type=' + intMediaType + '&userid=' + intUserID + '&lockerid=' + strLockerKEY ,
		data: { do: 'add2locker' },
		success: function(data) {
			// do something;
			//alert(data);
			
			if(data=='done'){ //if it worked
				
				//play sound? - other effect?
				parent.soundManager.play('lvlup','/images/sounds/itemuse.mp3'); //play sound

				//change virtue image of the item element 
				//parent.document.getElementById(strElementID).src = '/images/profile_gloss.gif' ;

				//bring up window layer
				//parent.openInIFrame_All('hud_givevirtue_window_iframe','hud_givevirtue_window','/ajax/virtuegivewindow.php?virtueid=' + intVirtue)
				

			}else{
			
			alert('add2locked failed - error: ' + data );
				
			}
	
		},	
	});
}



function changeTextColor(strItemName) {
	
	var e = document.getElementById(strItemName);
	//e.style.color = 'orange';
    if (e.style.color == '#F63')
        e.style.color = '#FFF';
		//strFrameLayerToggle = document.getElementById(id) + 'OFF' ;
    else
        e.style.color = '#F63';
		//strFrameLayerToggle = document.getElementById(id) + 'ON' ;
}

function loadContent(elementSelector, sourceURL) {
	//alert('loading ' + sourceURL + ' into ' + elementSelector);
	
	document.getElementById(elementSelector).style.display = 'block';
	
	$("#"+elementSelector+"").load(""+ sourceURL +"");
}

function loadpopup(url_str) {
	//called from 
	
	toggle_visibility('popup_container');
	
    //Make Stream layer visible
    //$('#frameDiv').show();

   // var preloader = '<div class="preloader"><img src="/images/loading.gif"></div>';
	
   document.getElementById('popup_container').innerHTML = '<iframe src="' + url_str + '" id=""></iframe></div>';

	}
	
function gotourl(strURL){
    window.location = strURL;
}
	
	
function getDocHeight() {
    var D = document;
    return Math.max(
        Math.max(D.body.scrollHeight, D.documentElement.scrollHeight),
        Math.max(D.body.offsetHeight, D.documentElement.offsetHeight),
        Math.max(D.body.clientHeight, D.documentElement.clientHeight)
    );
}


	
var strPreviousIframeURL = '';
var intOpenIframeCount = 0 ;
function openInIFrame_All(strIframeID,strDivID,strURL) { //open url in iframe 'iFrameID'
	
	document.getElementById(strIframeID).src=strURL; //change iframe to new url
	
	//if virgin click OR url are not same then toggle
	if( strPreviousIframeURL=='' || strURL==strPreviousIframeURL ){
		toggle_visibility(strDivID); //open window
		intOpenIframeCount = intOpenIframeCount + 1 ;
		}
		
	strPreviousIframeURL = strURL ;	//global 

	
	}


//var strFrameLayerToggle='';
//var e ='';


function reDir(oInput) {
    window.location = oInput;
	}

function jsfunctChangeHTML(strElementID, strHTML) {
    mydiv = document.getElementById(strElementID);
    mydiv.innerHTML = strHTML;
}

function hideWatermark(theID)
{
    var element = document.getElementById(theID);
    element.style.backgroundImage = 'none';
    element.style.backgroundColor = 'white';
}

function showWatermark(theID)
{
    var element = document.getElementById(theID);
    if (element.value.length == 0)
        element.style.backgroundImage = 'url(\'IMAGE_LOCATION_HERE\')';
    else
        element.style.backgroundColor = 'white';
}

function UrlExists(url)
{
    var http = new XMLHttpRequest();
    http.open('HEAD', url, false);
    http.send();
    return http.status!=404;
}


var TimeToFade = 1000.0;

function fade(eid)
{
  var element = document.getElementById(eid);
  if(element == null)
    return;
   
  if(element.FadeState == null)
  {
    if(element.style.opacity == null
        || element.style.opacity == ''
        || element.style.opacity == '1')
    {
      element.FadeState = 2;
    }
    else
    {
      element.FadeState = -2;
    }
  }
   
  if(element.FadeState == 1 || element.FadeState == -1)
  {
    element.FadeState = element.FadeState == 1 ? -1 : 1;
    element.FadeTimeLeft = TimeToFade - element.FadeTimeLeft;
  }
  else
  {
    element.FadeState = element.FadeState == 2 ? -1 : 1;
    element.FadeTimeLeft = TimeToFade;
    setTimeout("animateFade(" + new Date().getTime() + ",'" + eid + "')", 33);
  }  
}
function animateFade(lastTick, eid)
{  
  var curTick = new Date().getTime();
  var elapsedTicks = curTick - lastTick;
 
  var element = document.getElementById(eid);
 
  if(element.FadeTimeLeft <= elapsedTicks)
  {
    element.style.opacity = element.FadeState == 1 ? '1' : '0';
    element.style.filter = 'alpha(opacity = '
        + (element.FadeState == 1 ? '100' : '0') + ')';
    element.FadeState = element.FadeState == 1 ? 2 : -2;
    return;
  }
 
  element.FadeTimeLeft -= elapsedTicks;
  var newOpVal = element.FadeTimeLeft/TimeToFade;
  if(element.FadeState == 1)
    newOpVal = 1 - newOpVal;

  element.style.opacity = newOpVal;
  element.style.filter = 'alpha(opacity = ' + (newOpVal*100) + ')';
 
  setTimeout("animateFade(" + curTick + ",'" + eid + "')", 33);
}

function MM_preloadImages() { //v3.0
    var d = document;
    if (d.images) {
        if (!d.MM_p) d.MM_p = new Array();
        var i,j = d.MM_p.length,a = MM_preloadImages.arguments;
        for (i = 0; i < a.length; i++)
            if (a[i].indexOf("#") != 0) {
                d.MM_p[j] = new Image;
                d.MM_p[j++].src = a[i];
            }
    }
}
function MM_findObj(n, d) { //v4.01
    var p,i,x;
    if (!d) d = document;
    if ((p = n.indexOf("?")) > 0 && parent.frames.length) {
        d = parent.frames[n.substring(p + 1)].document;
        n = n.substring(0, p);
    }
    if (!(x = d[n]) && d.all) x = d.all[n];
    for (i = 0; !x && i < d.forms.length; i++) x = d.forms[i][n];
    for (i = 0; !x && d.layers && i < d.layers.length; i++) x = MM_findObj(n, d.layers[i].document);
    if (!x && d.getElementById) x = d.getElementById(n);
    return x;
}
function MM_swapImage() { //v3.0
    var i,j = 0,x,a = MM_swapImage.arguments;
    document.MM_sr = new Array;
    for (i = 0; i < (a.length - 2); i += 3)
        if ((x = MM_findObj(a[i])) != null) {
            document.MM_sr[j++] = x;
            if (!x.oSrc) x.oSrc = x.src;
            x.src = a[i + 2];
        }
}
function MM_swapImgRestore() { //v3.0
    var i,x,a = document.MM_sr;
    for (i = 0; a && i < a.length && (x = a[i]) && x.oSrc; i++) x.src = x.oSrc;
}