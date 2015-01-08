'use strict';

var eventRestServices = angular.module('eventRestServices', ['ngResource']);
eventRestServices.factory('Events', ['$resource',
	
	function ($resource) {
console.log("eventRestServices: activities.php/?pc=:pc&period=:period&radius=:radius");		
console.log("filter.searchDistances:");
//console.debug($scope.filter.searchDistances);
		return $resource('activities.php/?pc=:pc&period=:period&radius=:radius', {},{
			query : {
				method : 'GET',
				isArray: true,
				cache : true
			}
		});
	}]);
		
