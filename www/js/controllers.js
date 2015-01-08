'use strict';

var eventControllers = angular.module('eventControllers', ["checklist-model", "uiGmapgoogle-maps"]);

eventControllers.controller('DashCtrl', ['$scope', function ($scope) {
	console.log('DashCtrl'); 
}]);

eventControllers.controller('MainCtrl', ['$scope', '$rootScope', '$window', '$location', 'geoFactory', function ($scope, $rootScope, $window, $location, geoFactory) {
	$scope.slide = '';
	$rootScope.back = function() {
	  $scope.slide = 'slide-right';
	  $window.history.back();
	}
	$rootScope.go = function(path){
	  $scope.slide = 'slide-left';
	  $location.url(path);
	}
	
	$scope.filter = {};
	
	if (!$scope.filter.radius) {
		$scope.filter.radius = 40;
	}
console.log("scope.radius:"+$scope.filter.radius);
	$scope.searchDistances = [
		{ id:"5000", name:"5 km"}, 
		{ id:"8000", name:"5 Engelse mijl"},
		{ id:"10000", name:"10 km"}, 
		{ id:"15000", name:"15 km"}, 
		{ id:"16000", name:"10 Engelse mijl"},
		{ id:"21100", name:"Halve marathon"},
		{ id:"25000", name:"25 km"},
		{ id:"30000", name:"30 km"},
		{ id:"35000", name:"35 km"},
		{ id:"42200", name:"Marathon"}
	];
	
	$scope.filter.searchForDistance = [ ];
	$scope.filter.searchLocal =true;
		
	geoFactory.getPostalCodePlaats(function(pcPlaats) {
		$scope.filter.pc = pcPlaats[0].substring(0,4);
		$scope.filter.plaats = pcPlaats[1];
		$scope.filter.latitude = pcPlaats[2].latitude;
		$scope.filter.longitude = pcPlaats[2].longitude;
	});

}]).directive('eventDate', function() {
	//var dateArr = utils.parseDate($scope.event.displaydate);
	//var event;
	console.log("eventDate");
	console.debug(event);
    return {
		
      template: '<time class="icon">' +
//					'<em>{{dayName}}</em>' +
					'<strong>{{dayName}}</strong>' +
					'<span>{{dayOfMonth}}</span>' +
				'</time>', //'Date: {{event.displaydate}}'
		link: function (scope, element, attrs) {
			var arr = scope.event.date.split("/");
//console.debug(scope.event);
			//utils.parseDate(scope.event.date);
			var d = new Date(arr[2], (arr[1]-1), arr[0], 0, 0, 0, 0);
			scope.monthName = d.getMonthNameShort();
			scope.dayName = d.getDayNameShort();
			scope.dayOfMonth = arr[0];
		}
    };
  }).directive('eventDateBig', function() {
	//var dateArr = utils.parseDate($scope.event.displaydate);
	//var event;
	//console.log("eventDate");
	//console.debug(event);
    return {
		
      template: '<time class="iconbig">' +
					'<em>{{dayName}}</em>' +
					'<strong>{{monthName}}</strong>' +
					'<span>{{dayOfMonth}}</span>' +
				'</time>', //'Date: {{event.displaydate}}'
		link: function (scope, element, attrs) {
			var arr = scope.event.date.split("/");
			//utils.parseDate(scope.event.date);
			var d = new Date(arr[2], (arr[1]-1), arr[0], 0, 0, 0, 0);
			scope.monthName = d.getMonthName();
			scope.dayName = d.getDayName();
			scope.dayOfMonth = arr[0];
		}
    };
  });
	
eventControllers.controller('EventListCtrl', ['$scope', '$rootScope', '$stateParams', 'Events', 'MemEvents', 'geoFactory', function ($scope, $rootScope, $stateParams, Events, MemEvents, geoFactory) {

		var curDate = new Date();
		curDate.setDate(15);
		if (typeof $stateParams.period !== "undefined") {
			var arrPeriod = $stateParams.period.split("-");
			var curMonth = getMonthNumber(arrPeriod[0]);
			var curYear = arrPeriod[1];	
			curDate.setMonth(curMonth);
			curDate.setFullYear(curYear);
console.log("set periode("+curMonth+","+curYear+"):"+$stateParams.period);			
		}

		$scope.curMonth = curDate.getMonthName();
		$scope.curMonthYear = curDate.getMonthName() + '-' + curDate.getFullYear();
console.log("curMonthYear("+curDate+"):"+$scope.curMonthYear);
		curDate.setMonth(curDate.getMonth() + 1);
		$scope.nextMonthYear = curDate.getMonthName() + '-' + curDate.getFullYear();
console.log("nextMonthYear("+curDate+"):"+$scope.nextMonthYear);
		$scope.nextMonth = curDate.getMonthName();
		curDate.setMonth(curDate.getMonth() - 2);
		$scope.prevMonthYear = curDate.getMonthName() + '-' + curDate.getFullYear();
console.log("prevMonthYear("+curDate+"):"+$scope.prevMonthYear);
		$scope.prevMonth = curDate.getMonthName();
console.log("Radius:"+$scope.filter.radius);
console.debug($scope);
console.log("searchForDistance:");
console.debug($scope.filter);
		var period = $scope.curMonthYear;
console.log("test:"+$scope.filter.pc);		
		var distances = "all";
		if ($scope.filter.searchSpecificDistance) {
		
console.log("Dist:")
console.debug($scope.filter.searchForDistance.join(","));
		
			distances = $scope.filter.searchForDistance.join(",");
		}
		

		geoFactory.getPostalCodePlaats(function(pcPlaats) {
			$scope.filter.pc = pcPlaats[0].substring(0,4);
			$scope.filter.plaats = pcPlaats[1];
			$scope.filter.latitude = pcPlaats[2].latitude;
			$scope.filter.longitude = pcPlaats[2].longitude;
			
			var pc = $scope.filter.pc;
			if (!$scope.filter.searchLocal) {
				pc = "all";
			}	
			
			$scope.events = Events.query({pc:pc, period:$scope.curMonthYear, radius:$scope.filter.radius, distances:distances}, function() {
				console.log("Query done:");	
				console.debug($scope.events);
				
				$scope.gmap = {};
				$scope.gmap.center = {latitude: $scope.filter.latitude, longitude: $scope.filter.longitude};
				$scope.gmap.zoom = 12;
				$scope.gmap.dynamicMarkers = [];
				for (var id in $scope.events) {
					if ($scope.events[id].latitude !== null) {
console.log("$scope.events[id].latitude:"+$scope.events[id].latitude);
						$scope.gmap.dynamicMarkers.push({
							id: id,
							coords: { latitude: $scope.events[id].latitude,	longitude: $scope.events[id].longitude},
							title: $scope.events[id].title,
							onClick: function (marker) {
								//$('.gm-style-iw').parent().hide();
							    marker.showWindow = true;
								$scope.$apply();
								console.log("clk");
							}
						});
					}
				}
				$scope.gmap.onclick = function() {
					console.log("clicked");
				}

console.log("gmap:"); console.debug($scope.gmap);				
			});

			MemEvents.setEvents($scope.events);
		});


}]);

eventControllers.controller('EventDetailCtrl', ['$scope', '$stateParams', 'MemEvents', 'Events', 'calendarFactory', function ($scope, $stateParams, MemEvents, Events, calendarFactory) {
	console.log("stateParams");
	console.debug($stateParams);
	
	$scope.event = MemEvents.getEvent($stateParams.id);
	
	$scope.maps = { center: {latitude: event.lat, longitude: event.lng}, zoom: 12};
	
console.debug($scope.maps);
	/*
	$scope.addCalendarEvent = function () {
		console.log("addCalendarEvent...");
		calendarFactory.addCalendarEvent();
		return false;
	};
	$scope.$on('handleBroadcast', function () {
		console.log('handleBroadcast received');
		$scope.calendars = calendars;
	});
	
	*/
}]);



	
eventControllers.factory('geoFactory', function() {
	var factory = {};
	
	factory.getPostalCodePlaats = function(onDone) {
		factory.geocoder = new google.maps.Geocoder();
console.log("getPostalCode"+navigator.geolocation);
		factory.onDone = onDone;
		navigator.geolocation.getCurrentPosition(factory.currentPositionSuccess, factory.currentPositionError);
		//alert("done")
	};
	
	factory.currentPositionSuccess = function(position) {
		//alert('pos:' + position.coords.latitude + ","+ position.coords.longitude);
		var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
		factory.geocoder.geocode({
			'latLng': latlng
		}, function(results, status) {
console.log("geocode:"); console.debug(results);
console.debug(latlng.lat());
			var pcPlaats = factory._getPostalCode(results);
			pcPlaats[2] = { latitude: latlng.lat(), longitude: latlng.lng()};
console.log("pcPlaats:"); console.debug(pcPlaats);
			factory.onDone(pcPlaats);
	
		});
		
	};
	
	factory.currentPositionError = function(error) {
		console.error('code: '    + error.code    + '\n' + 'message: ' + error.message + '\n');	
	};

	factory._getPostalCode = function (results) {
		var pcPlaats = [];
		for (var i=0; i<results.length; i++) {
			for (var j=0; j<results[i].types.length; j++) {
console.log("Type:"+results[i].types[j]);			
				if (results[i].types[j] == "postal_code") {
					for (var k=0; k<results[i].address_components.length; k++) {
						for (var l=0; l<results[i].address_components[k].types.length; l++) {
							if (results[i].address_components[k].types[l] == "postal_code") {
								pcPlaats[0] = results[i].address_components[k].short_name;
							} else if (results[i].address_components[k].types[l] == "locality") {
								pcPlaats[1] = results[i].address_components[k].short_name;
								return pcPlaats;
							}
						}
					}
					
				}
			}

		}
		return pcPlaats;
	};
	return factory;
});
	
eventControllers.factory('calendarFactory', function($rootScope) {
	var factory = {};

	factory.clientId = '420520060585-f6nd8f8ol4b76rgknrfpn8furricur6q.apps.googleusercontent.com';
	factory.apiKey = 'AIzaSyCUdm8rO-3FlHMUoyrhO79n6Ay6CnTeqbQ';
	factory.scopes = 'https://www.googleapis.com/auth/calendar';
	

	factory.getCalendars = function() {
		console.log("addCalendarEvent2");
		gapi.auth.authorize({client_id: factory.clientId, scope: factory.scopes, immediate: false},	factory._getCalendars);
	}
	
	factory._getCalendars = function () {

		console.log("addCalendarEvent");
		console.log($scope);
	
		gapi.client.load('calendar', 'v3', function() {
			var request = gapi.client.calendar.calendarList.list({
			  'minAccessRole': 'owner'
			});
				  
			request.execute(function(resp) {
				for (var i = 0; i < resp.items.length; i++) {
					console.log("Calendar:"+resp.items[i].summary);
				}
				factory.calendars = resp.items;
				//$rootScope.$broadcast('handleBroadcast');
				//var calendarData = resp.items[0];

				//app.scope.event.displayName
				//app.ngDialog.open({ className: 'ngdialog-theme-default',
				//					data: app.scope,
				//					template: 'partials/calendar-dialog.html' });
				//app._addCalendarEvent('michelvanderheide@gmail.com', "test wedstrijd", "Goor", "2014-05-01", "2014-05-01");
				
			});

		});
	}
	return factory;

});
