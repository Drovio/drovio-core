/* 
 * Redback JS Document
 *
 * Title: RedBack JS Library - Gmap
 * Description: Control the gmap controls used by redback system
 * Author: RedBack Developing Team 
 * Version: 2.2
 * DateCreated: 11/06/2012
 * DateRevised: 6/08/2012
 *
 */

var jq = jQuery.noConflict();

// Declare the presence of this tool

/*++++++++++++++++++++++++Redback gMap Control root Namespace++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
rbGMap = {};
/*========================Redback gMap Control Map Object==========================================================*/
rbGMap.map = { 
	//Declare Global Variables
	errorExist : false,
	runTimes : 0,
	initRunning : false,
	loadingTimeOut : 10000, //Time to wait until maps loaded
	controlType : null,
	callBackFunc : null,
	mapDiv : '#rb_gMap',
	msg : new Array(),
	markersArray : new Array(),
	markersPreDefStyles : {
		"homeLocation" : {
							"image" : '../Library/Media/Elements/images/pages/ebuilder/homeLocation.png',
						},
	},
	geocoder : null,
	path : null,
	
	//Check Google Maps Loading Condition And Fires init's next step
	check :function(elapsedTime){
		if(typeof(google) =='undefined' && elapsedTime < rbGMap.map.loadingTimeOut){
			elapsedTime += 1000
			//console.log("Waiting Map to Load "+elapsedTime)
			setTimeout("rbGMap.map.check("+elapsedTime+")", 1000); // check again in a second
		}
		else{
			if (elapsedTime < rbGMap.map.loadingTimeOut){
				//console.log("Map OK")
				rbGMap.map.postInit();
			}
			else{
				alert("Timeout Error");
				//console.log("TimeOut")
			}
		}
	},
	
	//Check If map is fully loaded in mapDiv
	checkMapExistance:function(){
		if (!typeof(jq(rbGMap.map.mapDiv).html()) == null){
			if (jq(rbGMap.map.mapDiv).html().length > 0) 
				return true
			else
				return false
		}
		else
			return false;
	},
	
	//Initalize google Maps api and redback map behavior
	//mapReload - Indicates whatever is the first or not time that map loads in page context
	init:function(mapReload){
		rbGMap.map.runTimes +=1;
		rbGMap.map.initRunning = true;
		//console.log("Times script Init run = " + rbGMap.map.runTimes);
		rbGMap.map.errorExist = false;
		rbGMap.map.preInit()
		if (rbGMap.map.errorExist){
			//console.log("Exiting Init with PreInit Error");
			rbGMap.map.initRunning = false;
			return false;
		}
		else{
			if (rbGMap.map.callBackFunc == null){
				rbGMap.map.initRunning = false;
				return false;
			}
			if(typeof(google) =='undefined')
				jq.getScript("http://maps.googleapis.com/maps/api/js?sensor=false&async=2&callback=rbGMap.loader."+rbGMap.map.callBackFunc);
			if(mapReload && typeof(google) !='undefined')
				rbGMap.loader[rbGMap.map.callBackFunc]();
			//Halts execution until Map Api is fully loaded
			var elapsedTime = 0;
			rbGMap.map.check(elapsedTime);
		}
	},

	
	preInit: function() {
		//Search Attributes And Init Control
		var controlContainer = jq('#rb_gMapHolder');
		if (!controlContainer.length > 0) {
			//console.log("redBack_gMapControl NOT FOUND - Exiting Script")
			rbGMap.map.errorExist = true;
			return false;
			//console.log("Script Still Running")
		}
		
		var controlContent = jq('#rb_gMapContent');
		var controlReport = jq('#rb_gMapReport');
		var notificationPool = jq('#rb_gMapNotificationPool');
		
		//Initialize notification message objects
		notificationPool.find('span').each(function() {
			rbGMap.map.msg[jq(this).attr('id')] = jq(this);
		});
		
		//Get Map Control Attributes
		rbGMap.map.controlType = controlContainer.attr('data-controlType');
		controlContainer.removeAttr('data-controlType');
		
		//Set CallBack Function
		if(typeof(rbGMap.map.controlType )=='undefined' || rbGMap.map.controlType == null)
			rbGMap.map.errorExist = true;
		else
			rbGMap.map.callBackFunc = "loadMap_" + rbGMap.map.controlType; 
	},

	postInit : function() {
		//Switch Behavor Throught Control Type
		var controlContainer = jq('#rb_gMapHolder');
		switch (rbGMap.map.controlType) { 
			case 'addressFinder':
				//Declare Events
				queryField = jq('#addressQueryField' )
				queryField.off("geoLocSearch");
				queryField.on("geoLocSearch", function(){
					rbGMap.func.codeAddress(jq(this).val(),"setAddress") 
				});
				
				//Assing Events
				if (controlContainer.data("prefs").actionControl) {
					jq(document).off('click', '#execButton');
					jq(document).on('click', '#execButton',function(){
						jq('#addressQueryField').trigger("geoLocSearch");
					});
				}
				
				controlContainer.removeAttr('data-prefs');
				break;
			case 'setArea':
				//Built Map
				//mapDiv = jq("<div>");
//				mapDiv.attr( 'id', 'rb_gMap' )
//				mapDiv.addClass('deliveryMap') 
//				controlContent.append(mapDiv);
//				
//				//Built Map
//				mapDiv = jq("<div>");
//				mapDiv.attr( 'id', 'gMapPointList' )
//				controlContent.append(mapDiv);

				//Add OnClick Map Event
				google.maps.event.addListener(map, 'click', rbGMap.events.addPolygonPoint);
				
				//Get predefined area
				var controlResult = jq('#rb_gMapResult');
				controlResult.find('input').each(function(){
					id = jq(this).attr('id').split('_');
					idNo = id [1];
					lat = jq(this).data("cords").lat;
					lng = jq(this).data("cords").lng;
					latLng = new google.maps.LatLng(lat, lng),
					rbGMap.func.addPolygonPoint(latLng, false);
				});
	
				break;
			case 'areaFinder':
				//Set User Home Location
				var homeLocation = jq('.curAddress');
				var homelat = homeLocation.data("homecord").homelat;
				var homelng = homeLocation.data("homecord").homelng;
				
				rbGMap.func.addMarker(homelat, homelng, 'clear', "homeLoc", null, rbGMap.map.markersPreDefStyles['homeLocation'])				
				//Find Address
				rbGMap.func.codeLatLng(homelat,homelng,"populateCurrentAddress");
				
				//Declare Events
				queryField = jq('#addressQueryField' )
				queryField.off("geoLocSearch");
				queryField.on("geoLocSearch", function(){
					rbGMap.func.codeAddress(jq(this).val(),"setHomeLoc") 
				});
				
				//Assing Events
				jq(document).off('click', '#execButton');
				jq(document).on('click', '#execButton',function(){
					jq('#addressQueryField').trigger("geoLocSearch");
				});
				
				jq('.myAddress').each(function(){
					jq(this).click(function(){
						var homelat = jq(this).data("homecord").homelat;
						var homelng = jq(this).data("homecord").homelng;
						//Add Marker
						rbGMap.func.addMarker(homelat, homelng, 'clear', "homeLoc", null, rbGMap.map.markersPreDefStyles['homeLocation'])				
						//Find Address and show in Proper Div
						rbGMap.func.codeLatLng(homelat,homelng,"populateCurrentAddress");
					});
				});
				
				//Set Filter Categories Events
				jq('.filterCollection').children('div.navMenu').children('ul').children('li').off('click',rbGMap.events.OnFilterCollectionClick);
				jq('.filterCollection').children('div.navMenu').children('ul').children('li').on('click',rbGMap.events.OnFilterCollectionClick);

	
				break;
			case 'pinView':
				xmlSource = controlContainer.data("prefs").xmlSource;
				markerOptions = {
							draggable: false
						}
				markerStyle = null
				markerEvents = new Object;
				markerEvents['click'] = function(marker){
					return function(){
						var contentString = '<div id="content">' +
							'<span>This A Test Sample</span>' +
							'</div>';
						var infowindow = new google.maps.InfoWindow({
							content: contentString
						});
						infowindow.open(map,marker);		
					}
				};
				if(xmlSource !="" && xmlSource!=null)
					showMarkersFromXML("xmlSource", markerOptions, markerStyle, markerEvents);
				break;
			default :
				//console.log("You cannot call this manually")
				rbGMap.map.errorExist = true;
				return false;
				break;
		}
		rbGMap.map.initRunning = false;
	}

}; 
/*========================Redback gMap Control Map Loaders=========================================================*/
rbGMap.loader = {
	  mapDivID : 'rb_gMap',
	  optionSet : null,

	
/*----------loadAddressFinderMap----------
	*Initalize the map control Option for geolocation Address Searching
	*Input:
	*Return: 
	*		void
	*/
	loadMap_addressFinder : function() 
	{
	rbGMap.loader.optionSet = {
		zoom: 6,
		center: new google.maps.LatLng(40.22, 22.51),
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		streetViewControl: false
	  };

	  rbGMap.func.loadMapControl(rbGMap.loader.mapDivID, rbGMap.loader.optionSet);
	  rbGMap.func.createGeocoder();
	},
	
	/*----------loadDeliveryAreaMapSelector----------
	*Creates map into the map div
	*Input:
	*Return: 
	*		void
	*/
	loadMap_setArea : function () 
	{
	  rbGMap.loader.optionSet = {
		zoom: 12,
		center: new google.maps.LatLng(40.22, 22.51),
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		streetViewControl: false
	  };
	  rbGMap.func.loadMapControl(rbGMap.loader.mapDivID, rbGMap.loader.optionSet);
	  rbGMap.func.createPolygon();
	},
	
	/*----------loadAreaFinderMap----------
	*Creates map into the map div
	*/
	loadMap_areaFinder : function () 
	{
	  rbGMap.loader.optionSet = {
		zoom: 6,
		center: new google.maps.LatLng(40.22, 22.51),
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		streetViewControl: false
	  };
	  rbGMap.func.loadMapControl(rbGMap.loader.mapDivID, rbGMap.loader.optionSet);
	  rbGMap.func.createGeocoder();
	},
	
	/*----------loadMap_pinView----------
	*Load a Simple Pin View Map Control
	*/
	loadMap_pinView : function () 
	{
	  rbGMap.loader.optionSet = {
		zoom: 6,
		center: new google.maps.LatLng(40.22, 22.51),
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		streetViewControl: false
	  };
	  rbGMap.func.loadMapControl(rbGMap.loader.mapDivID, rbGMap.loader.optionSet);
	}

 };
 
/*========================Redback gMap Control Functions===========================================================*/
rbGMap.func = {
	
/*------------------------Redback gMap Control General Functions --------------------------------------------------*/
	/*
	*----------loadMapControl----------
	*Creates map into the map div
	*Input:
	*		mapDivID : The ID of map container
	*		optionSet : array of map options
	*Return: 
	*		void
	*/
	loadMapControl : function (mapDivID, mapOptionsSet) 
	{
		var mapDiv = document.getElementById(mapDivID);
		map = new google.maps.Map(mapDiv,mapOptionsSet);
		// If map is visible(jquery selector) and triggers resize event to show map properly
		mapVisibilityLoop = setInterval(function(){
			if(jq('#'+mapDivID).filter(":visible").length > 0){
				var currCenter = map.getCenter();
				google.maps.event.trigger(map, 'resize');
				map.setCenter(currCenter);
				clearInterval(mapVisibilityLoop)
				return false;
				}
			}, 1000); // check again in a second
	},
	
	/*----------addMarker----------
	*Creates map into the map div event
	*Input: Event Data Bundle
	*		lat : 
	*		lng :
	 appendType : clear or append
	*Return: 
	*		int : the inserted marker position in markersArray
	*		OR -1 if error exists
	*/
	addMarker : function (lat, lng, appendType, collectionCode, markerOptions, markerStyle, markerEvents){
		//console.log("---Add Marker Proccess")
		//___Giving a default value null to args if they are undefined
		lat = (typeof lat == 'undefined') ? null : lat;
		lng = (typeof lng == 'undefined') ? null : lng;
		
		//___Check lat,lng
		if(lat == null || lng == null)
		{
			//console.log("	Error, null lat or lng")
			return -1
		}
		
		var LatLngPos = new google.maps.LatLng(lat, lng);
		map.setCenter(LatLngPos);
		
		//___Check appendType
		switch (appendType) { 
			case 'clear':
				rbGMap.func.deleteOverlays(collectionCode);
				break;
			case 'clearALL':
				rbGMap.func.deleteOverlays();
				break;
			default :
				break;
		}
		
		//___Check markerOptions
		defaultMarkerOptions = {
			map: map,
			position: LatLngPos,
			draggable: true
		}
		if(typeof(markerOptions) != 'undefined' && markerOptions != null)
			jq.extend(defaultMarkerOptions, markerOptions);
		
		//___Check markerStyle
		//console.log("	markerStyle = " + jq.type(markerStyle))
		if(typeof(markerStyle) != 'undefined' && markerStyle != null){
			style = {
				icon: new google.maps.MarkerImage(
					markerStyle["image"],
					null, /* size is determined at runtime */
					null, /* origin is 0,0 */
					null, /* anchor is bottom center of the scaled image */
					// This marker is 20 pixels wide by 32 pixels tall.
					null//new google.maps.Size(32, 32)
				)
			}
			jq.extend(defaultMarkerOptions, style);	
		}
		
		//___Create Marker
		marker = new google.maps.Marker(defaultMarkerOptions);							
		
		//___Check for markerEvents
		for (var ev in markerEvents)
		{
			google.maps.event.addListener(marker, ev, markerEvents[ev](marker))
		}
		
		//___Assing Marker to MarkerArray and Exit
		//console.log("	new Marker collection code = " + collectionCode);
		//console.log("	Must say array : " + jq.type(rbGMap.map.markersArray[collectionCode]));
		if (jq.type(rbGMap.map.markersArray[collectionCode])!="array")			
			rbGMap.map.markersArray[collectionCode] = new Array();
		
		len = rbGMap.map.markersArray[collectionCode].push(marker);
		return len-1;
	},
	  
	/*
	*----------addPolygonPoint----------
	*Creates a Polygon(area) point and shows a marker in the map, assinging all proper events
	*Creates an input field in result section with point info if it asked.
	*Input:
	*		latLng : gMap latLng object for point cords an input field in result section with point info
	*		createField : true/false, creates or not
	*Return: 
	*		void
	*/
	addPolygonPoint : function (latLng, createField){
		markerColCode = "poly_12345";
		
		lat = latLng.lat();
		lng = latLng.lng();

		rbGMap.map.path.insertAt(rbGMap.map.path.length, latLng);
		
		//Declaring Marker Events in a new markerEvents Object key => value
		//Where key is the event name and value is function to execute when events fires		
		markerEvents = new Object;
		markerEvents['click'] = function(marker){
			return function(){		
				marker.setMap(null);
				for(var collection in rbGMap.map.markersArray) {
					if(collection.indexOf("poly_") >= 0){
						tempArray = rbGMap.map.markersArray[collection];
						for (var i = 0; i < tempArray.length && tempArray[i] != marker; ++i);
						tempArray.splice(i, 1);
						rbGMap.map.path.removeAt(i);
						rbGMap.func.rebuildHidField(jq('#rb_gMapResult'), tempArray);
						rbGMap.map.markersArray[collection] = tempArray;
					}
				}
			}
		};
		markerEvents['dragend'] = function(marker){
			return function(){
				for(var collection in rbGMap.map.markersArray) {
					if(collection.indexOf("poly_") >= 0){
						tempArray = rbGMap.map.markersArray[collection];
						for (var i = 0; i < tempArray.length && tempArray[i] != marker; ++i);
						rbGMap.map.path.setAt(i, marker.getPosition());
						rbGMap.func.changeHidField(marker.getPosition().lat(),marker.getPosition().lng(), i+1);
						rbGMap.map.markersArray[collection] = tempArray;
					}
				}
			}
		};
		
		pos = rbGMap.func.addMarker(lat, lng, "append", markerColCode, null, null, markerEvents);
		
		polygonAreaArray = rbGMap.map.markersArray[markerColCode];
		marker = polygonAreaArray[pos];
		marker.setTitle("#" + rbGMap.map.path.length);

		if(createField)
			rbGMap.func.addHidField(jq('#rb_gMapResult'), lat, lng, rbGMap.map.path.length);
	},
	
	/*----------subFilterBehaviour----------
	*Find the subfilter itames of a filterDiv,
	* assings them events and behaviour on filter(parent) click
	*Input:
	*		filterDiv
	*Return: 
	*		void
	*/
	subFilterBehaviour : function (filterDiv) {
		fltCode = filterDiv.data("fltcode");
		filterDiv.find('.subFilterItem > .statusIndicator > input:checkbox').off('change');
		filterDiv.find('.subFilterItem > .statusIndicator > input:checkbox').on('change',rbGMap.events.OnSubFilterClick);

		if(jq.isArray(rbGMap.map.markersArray[fltCode])){
			rbGMap.func.showOverlays(fltCode);
		}
		else{
			filterDiv.find('.subFilterItem').each(function(){
				if (jq(this).find('.statusIndicator > input:checkbox').prop('checked')){
					jq(this).find('pinPool > div').each(function(){
						name = jq(this).data("pininfo").name;
						lat = jq(this).data("pininfo").lat;
						lng = jq(this).data("pininfo").lng;
						rbGMap.func.addMarker(lat,lng, 'append', fltCode);	
					});
				}
			});
		}

	},
	
	showMarkersFromXML : function (xmlSource, markerOptions, markerStyle, markerEvents) {
		f_pvar = "";
		_method = "GET";
		ansType = "xml";
		
		jq.ajax({
			url: _bootHost + xmlSource,
			data: f_pvar,
			type: _method,
			dataType: ansType,
			success: function(data) {
				//Start - Xml Response Reader
				jq(this).find('pin').each(function(){
					temp_pin = preSet_pin.clone();
					branchName = jq(this).children('branchName').text();
					branchLat = jq(this).children('branchLat').text();
					branchLng = jq(this).children('branchLng').text();
					
					addMarker(branchLat, branchLng, "append", "branchPoints", markerOptions, markerStyle, markerEvents)
				//End - Xml Response Reader
				});
			},//Success func of inner Ajax(read xml subfilter data)
		});
	},
	  
	/*----------deleteOverlays----------
	//*Creates geocoder class object into geocoder global variable
	//*Input:
	//*		null
	//*Return: 
	//*		void
	*/
	deleteOverlays : function (collectionCode) {
		if(collectionCode){
			tempArray = rbGMap.map.markersArray[collectionCode];
			if (tempArray){
				//console.log("===== " +tempArray);
				for (i in tempArray)
					 tempArray[i].setMap(null);
				delete rbGMap.map.markersArray[collectionCode];
			}
		}
		else{
			for (tempArrayKey in rbGMap.map.markersArray){
				//console.log("+++++ " +tempArrayKey);
				tempArray = rbGMap.map.markersArray[tempArrayKey]
				for (tmpMarkKey in tempArray)
				{
					tmpMark = tempArray[tmpMarkKey]
					tmpMark.setMap(null);
				}
				delete rbGMap.map.markersArray[tempArrayKey];
			}
		}
	},
	
	showOverlays : function (collectionCode){
		if(collectionCode){
			tempArray = rbGMap.map.markersArray[collectionCode];
			if (tempArray){
				for (i in tempArray)
					 tempArray[i].setMap(map);
			}
	  }
	},

	
/*------------------------Redback gMap Control Geocoding Functions ------------------------------------------------*/
	/*
	*----------createGeocoder----------
	*Creates geocoder class object into geocoder global variable
	*Input:
	*		null
	*Return: 
	*		void
	*/
	createGeocoder : function  ()
	{
	  rbGMap.map.geocoder = new google.maps.Geocoder();
	  return rbGMap.map.geocoder;
	},

	/*
	*----------codeAddress----------
	*Creates map into the map div
	*Input:
	*		queryField : 
	*Return: 
	*		void
	*/
	codeAddress : function (address, eventCallback) {
		rbGMap.map.geocoder.geocode( { 'address': address }, 
		  function(results, status){
			var container = jq('#rb_addressTlb');
			var ntfCnt = jq('#rb_notification');
			container.empty();
			ntfCnt.empty();
			if (status == google.maps.GeocoderStatus.OK) 
			{
				if (results.length == 0 )
				{
					ntfCnt.append(rbGMap.map.msg['addrNoFound']);
					return;
				}
				for(i=0; i < results.length; i++)
				{
					var sigleAddressDiv = jq("<div>");
					sigleAddressDiv.attr( 'id', 'address'+ i )
					sigleAddressDiv.addClass('gMapAddress') 
					container.append(sigleAddressDiv);
					
					var addressText = jq(document.createElement('span'));
					addressText.css('clear','both');
					addressText.css('display','block'); 
					addressText.html(results[i].formatted_address);
					sigleAddressDiv.append(addressText);
					
					var locationText = jq(document.createElement('span'));
					locationText.css('clear','both');
					locationText.css('display','block'); 
					locationText.html(results[i].geometry.location.toString());
					sigleAddressDiv.append(locationText);
					
					 //Assing Events
					sigleAddressDiv.off('click');
					sigleAddressDiv.on('click',{ result : results[i], type : eventCallback}, rbGMap.events.onAddressResultClick );//////////////
				  }
			} 
			else if (status == google.maps.GeocoderStatus.ZERO_RESULTS) 
			{
			  //indicates that the geocode was successful but returned no results.
			  //This may occur if the geocode was passed a non-existent address or a latlng in a remote location.
			  //container.innerHTML = "Nothing Found";
	//		  var error = jq(document.createElement('span'));
	//		  error.html("Nothing Found");
	//		  ntfCnt.append(error);
			  ntfCnt.append(rbGMap.map.msg['addrNoFound']);
	
			}
			else if (status == google.maps.GeocoderStatus.OVER_QUERY_LIMIT) 
			{
			  //indicates that you are over your quota.
			  //container.innerHTML = "over your quota";
			  var error = jq(document.createElement('span'));
			  error.html("over your quota");
			  ntfCnt.append(error);
	
			}
			else if (status == google.maps.GeocoderStatus.REQUEST_DENIED) 
			{
			  //indicates that your request was denied, generally because of lack of a sensor parameter.
			  //container.innerHTML = "Check Sensor";
			  var error = jq(document.createElement('span'));
			  error.html("Check Sensor");
			  ntfCnt.append(error);
	
			}
			else if (status == google.maps.GeocoderStatus.INVALID_REQUEST) 
			{
			  //generally indicates that the query (address or latlng) is missing.
			  //container.innerHTML = "You must provite an address";
			  var error = jq(document.createElement('span'));
			  error.html("You must provite an address");
			  ntfCnt.append(error);
			}
			else
			{
			  //container.innerHTML = "Unexpected error please refresh";
			  var error = jq(document.createElement('span'));
			  error.html("Unexpected error please refresh");
			  ntfCnt.append(error);
			}
		  }
	  );
	  },
	  
	  codeLatLng : function(lat, lng, call)  {
		var latlng = new google.maps.LatLng(lat, lng);
		rbGMap.map.geocoder.geocode({'latLng': latlng}, function(results, status) {
		  if (status == google.maps.GeocoderStatus.OK) {
			if (results[1]) {
			  rbGMap.func.populateCurrentAddress(results[1].formatted_address);
			} else {
			  //console.log("No results");
			  return false;
			}
		  } else {
			//console.log("Exit, error code " + status);
			return false;
		  }
		});
	  },

/*------------------------Redback gMap Control Polygon Functions --------------------------------------------------*/
	/*
	*----------createPolygon----------
	*Creates geocoder class object into geocoder global variable
	*Input:
	*		null
	*Return: 
	*		void
	*/
	createPolygon : function  ()
	{
	
	  poly = new google.maps.Polygon({
		strokeWeight: 3,
		fillColor: '#5555FF'
	  });
	  rbGMap.map.path = new google.maps.MVCArray;
	  poly.setMap(map);
	  poly.setPaths(new google.maps.MVCArray([rbGMap.map.path]));
	},

/*------------------------Redback gMap Control Element Builders Functions -----------------------------------------*/
	addHidField : function (pointArea, lat, lng, idNo)
	{
		markerField = jq('<input>');
		markerField.attr('id', 'marker_' + idNo )
		markerField.attr('type', 'textbox'); 
		markerField.attr('value', lat+":"+lng); 
		markerField.attr('disabled', 'disabled');
		pointArea.append(markerField);
	},
	rebuildHidField : function (pointArea, array)
	{
		pointArea.empty();
		for(i=0; i < array.length; i++)
		{
			var idNo = i +1 ;
			var lat = array[i].getPosition().lat();
			var lng = array[i].getPosition().lng();
			rbGMap.func.addHidField(pointArea, lat, lng, idNo);
		}
	},
	changeHidField : function (lat, lng, idNo)
	{
		document.getElementById("marker_"+idNo).value = lat+":"+lng;
	},
	
	populateCurrentAddress : function(addressText, lat, lng){
		var homeLocation = jq('.curAddress');
		
		//Built Address span
		addressSpan = jq("<span>");
		addressSpan.text(addressText);
		homeLocation.html(addressSpan);
		homeLocation.css("background","green");
		
		homeLocation.data("homecord").homelat = lat;
		homeLocation.data("homecord").homelng = lng;
},

/*------------------------Redback gMap ControlUnCategorizied Functions --------=-----------------------------------*/
	setLatLng : function (latContainerID,lngContainerID, latValue, lngValue)
	{
		var lat = document.getElementById(latContainerID);
		var lng = document.getElementById(lngContainerID);
		
		lat.value = latValue;
		lng.value = lngValue;
		
		}	,						
	
	onKeyUp_FillAddress : function ()
	{
		document.getElementById(arguments[0]).value = "";
		for(i=1; i < arguments.length; i++)
		{
		  document.getElementById(arguments[0]).value += " " + document.getElementById(arguments[i]).value;
		}
	},							
	
	//Cross Browser Add Event Listener
	addEvent : function (evnt, elem, func) {
		if (elem.addEventListener)  // W3C DOM
			elem.addEventListener(evnt,func,false);
		else if (elem.attachEvent) { // IE DOM
			 var r = elem.attachEvent("on"+evnt, func);
		return r;
		}
		else window.alert('I\'m sorry Dave, I\'m afraid I can\'t do that.');
	}
 };
 
/*------------------------Redback gMap Events --------=-----------------------------------*/
rbGMap.events = {
	
	/*
	*----------onAddressResultClick----------
	*A custom event, 
	*/
	onAddressResultClick : function(event){
		switch (event.data.type) {
			case 'setHomeLoc' :
			result = event.data.result;
			lat = result.geometry.location.lat();
			lng = result.geometry.location.lng();
			rbGMap.func.populateCurrentAddress(result.formatted_address,lat,lng);
			rbGMap.func.addMarker(lat, lng, 'clear', "homeLoc", null, rbGMap.map.markersPreDefStyles['homeLocation']);	
			
			break;
			case 'setAddress' :
			result = event.data.result;
			lat = result.geometry.location.lat();
			lng = result.geometry.location.lng();
			rbGMap.func.setLatLng("lat","lng",lat,lng)
			rbGMap.func.addMarker(lat,lng, 'clear', "homeLoc", null, null, null);	
			break
			default:
			
			break;
		}
	
	},
	
	/*
	*----------addMarker----------
	*A custom event, calls func.addMarker when occured
	*/
	addMarker : function (event) {
		lat = event.data.latCord;
		lng = event.data.lngCord;
		collectionCode = event.data.collectionCode;
		rbGMap.func.addMarker(lat,lng, 'clear', collectionCode);	
	},
	
	/*
	*----------addPoint----------
	*A gMap event, calls func.addPolygonPoint when occured
	*/
	addPolygonPoint : function (event){
		latLng = event.latLng;
		rbGMap.func.addPolygonPoint(latLng, true);
	},
		
	OnFilterCollectionClick : function (event){
		filterDiv = jq('#'+jq(this).data("static-nav").ref)
		fltCode = filterDiv.data("fltcode");
		homeLocMarker = rbGMap.map.markersArray["homeLoc"]
		rbGMap.func.deleteOverlays();
		rbGMap.map.markersArray["homeLoc"] = homeLocMarker;
		rbGMap.func.showOverlays("homeLoc");
		
		//If subfilter items does not exists, build them from XML
		if(!filterDiv.find('.subFilterItem').length > 0){
			_bootHost="";
			filterFile = "faspp";
			f_pvar = "";
			_method = "GET";
			ansType =  "html";
			
			jq.ajax({
				url: _bootHost+"/ajax/apps/aux_content.php?app=4&fl=ajax::"+filterFile,
				data: f_pvar,
				type: _method,
				dataType: ansType,
				success: function(data) {
					preSet_subFilter = jq(data).children( ".subFilterItem");
					preSet_pin = preSet_subFilter.find('.pinPool > div').clone();
					preSet_subFilter.find('.pinPool').empty()
					
					filterFile = filterDiv.attr("id") + ".php";
					f_pvar = "";
					_method = "GET";
					ansType = "xml";
					
					jq.ajax({
						url: _bootHost+"/ajax/domains/deliverynet/" + filterFile,
						data: f_pvar,
						type: _method,
						dataType: ansType,
						success: function(data) {
							//Start - Xml Response Reader
							jq(data).find('cat').each(function(){
								temp_subFilter = preSet_subFilter.clone();
								curPinPool = temp_subFilter.find('.pinPool');
								title = jq(this).attr('description');
								//console.log(title);
								temp_subFilter.find('.statusIndicator > label > span').html(title);
								rand = Math.floor(Math.random()*9999); 
								temp_subFilter.attr('data-subfltcode', rand);
								jq(this).find('pin').each(function(){
									temp_pin = preSet_pin.clone();
									branchName = jq(this).children('branchName').text();
									branchLat = jq(this).children('branchLat').text();
									branchLng = jq(this).children('branchLng').text();
							
									//temp_pin.data("pininfo", Object = {"name":branchName, "lat":branchLat, "lng":branchLng})
									
									temp_pin.data("pininfo").name = branchName;
									temp_pin.data("pininfo").lat = branchLat;
									temp_pin.data("pininfo").lng = branchLng;
									curPinPool.append(temp_pin);
								});
								filterDiv.append(temp_subFilter);
								});
							//End - Xml Response Reader
							rbGMap.func.subFilterBehaviour(filterDiv);
						},//Success func of inner Ajax(read xml subfilter data)
					});
				},//Success func of outer Ajax(read html subfilter structure)
			});
		}
		else
		{
			//Show subfilter items on map, and declaring behaviour
			rbGMap.func.subFilterBehaviour(filterDiv)
		}
	},
	
	OnSubFilterClick : function (event){
		//console.log("---Sub Filter Clicked")
		pinPool = jq(this).parent().siblings('.pinPool');
		subfltCode = jq(this).parent().parent().data("subfltcode");
		fltCode = jq(this).parent().parent().parent().data("fltcode");
		if (jq(this).prop('checked')){ //Not Checked But checked OnClick
			//console.log("	SubFilter is ON : "+jq(this).prop('checked'))
			pinPool.children('div').each(function(){
				name = jq(this).data("pininfo").name;
				lat = jq(this).data("pininfo").lat;
				lng = jq(this).data("pininfo").lng;
				//console.log("	PreCreateMarker " +" "+ lat +", "+ lng +", "+ fltCode)
				rbGMap.func.addMarker(lat, lng, 'append', fltCode +":"+subfltCode);	
			});
		}
		else{
			//console.log("	SubFilter is OFF : "+jq(this).prop('checked'))
			rbGMap.func.deleteOverlays(fltCode +":"+subfltCode);
		}		
	},

};


jq(document).one("ready.extra",function() { 
	//console.log("---rbGMap Start")
	//console.log("	Init Map for 1st time")
	var rbGMapObject = Object.create(rbGMap.map);
	rbGMapObject.init(false);
	jq(document).on("content.modified",function() {
		//console.log("---Content Modified Occured")
		//console.log("	Check If object exists")
		if(typeof(rbGMapObject) !='undefined'){
			//console.log("		Object Exists");
			//console.log("	Check If Init() is Running -- " + rbGMap.map.initRunning);
			//console.log("	Check If Map exists in Div -- " + rbGMap.map.checkMapExistance());	
			if (!rbGMap.map.checkMapExistance() && !rbGMap.map.initRunning){
			//console.log("---Init Is Not Running, Map does Not Exist");
			//console.log("	Try Reload Map");	
				rbGMapObject.init(true)
			}
		}
	});
	
	jq(document).trigger("content.modified");
});