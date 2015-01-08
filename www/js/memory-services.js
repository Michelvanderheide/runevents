'use strict';

var eventMemoryServices = angular.module('eventMemoryServices', []);

eventMemoryServices.factory('MemEvents', [ 	function () {
		var factory = {};
		
		factory.setEvents = function(events) {

			console.log("setEvents...");
			console.debug(events);
			factory.events = events;
			console.log(factory.events);
		}
		
		//factory.events = $scope.events;
		factory.getEvent = function(id) {
console.log("get event...");
			return factory.findById(parseInt(id));
		};
		
		factory.findById = function (id) {
console.log("findById:");
			    var event = null,
                l = factory.events.length,
                i;
            for (i = 0; i < l; i = i + 1) {
			
                if (factory.events[i].id == id) {
                    event = factory.events[i];
console.debug(event);				
console.log("latitude:"+event.latitude);
					event.gmap = {};
					event.gmap.center = {latitude: event.latitude, longitude: event.longitude};
					event.gmap.zoom = 12;
					event.gmap.dynamicMarker = {
						id: 1,
						coords: { latitude: event.latitude,	longitude: event.longitude},
						title:event.title
					};
					
					event.gmap.windowOptions = {
						visible: false
					};


                    break;
                }
            }
			
console.debug(event);
            return event;
		}
		return factory;
	}]);

