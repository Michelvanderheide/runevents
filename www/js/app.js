// Ionic Starter App

// angular.module is a global place for creating, registering and retrieving Angular modules
// 'starter' is the name of this angular module example (also set in a <body> attribute in index.html)
// the 2nd parameter is an array of 'requires'
// 'starter.services' is found in services.js
// 'starter.controllers' is found in controllers.js
angular.module('eventApp', ['ionic', 'eventControllers', 'eventMemoryServices', 'eventRestServices'])

.run(function($ionicPlatform) {
  $ionicPlatform.ready(function() {
    // Hide the accessory bar by default (remove this to show the accessory bar above the keyboard
    // for form inputs)
    if(window.cordova && window.cordova.plugins.Keyboard) {
      cordova.plugins.Keyboard.hideKeyboardAccessoryBar(true);
    }
    if(window.StatusBar) {
      // org.apache.cordova.statusbar required
      StatusBar.styleDefault();
    }
  });
})

.config(function($stateProvider, $urlRouterProvider) {

  // Ionic uses AngularUI Router which uses the concept of states
  // Learn more here: https://github.com/angular-ui/ui-router
  // Set up the various states which the app can be in.
  // Each state's controller can be found in controllers.js
  $stateProvider

    // setup an abstract state for the tabs directive
    .state('tab', {
      url: "/tab",
      abstract: true,
      templateUrl: "partials/tabs.html"
    })

    // Each tab has its own nav history stack:

    .state('tab.dash', {
      url: '/dash',
      views: {
        'tab-dash': {
          templateUrl: 'partials/tab-dash.html',
          controller: 'DashCtrl'
        }
      }
    })

    .state('tab.events', {
      url: '/events',
      views: {
        'tab-events': {
          templateUrl: 'partials/event-list.html',
          controller: 'EventListCtrl'
        }
      }
    })
    .state('tab.events-month', {
      url: '/events/period/:period',
      views: {
        'tab-events': {
          templateUrl: 'partials/event-list.html',
          controller: 'EventListCtrl'
        }
      }
    })	
    .state('tab.event-detail', {
      url: '/event/:id',
      views: {
        'tab-events': {
          templateUrl: 'partials/event-details.html',
          controller: 'EventDetailCtrl'
        }
      }
    })
    .state('tab.map-detail', {
      url: '/map/:id',
      views: {
        'tab-map': {
          templateUrl: 'partials/event-details.html',
          controller: 'EventDetailCtrl'
        }
      }
    })	
	.state('tab.map', {
      url: '/map',
      views: {
        'tab-map': {
          templateUrl: 'partials/event-map.html',
          controller: 'EventListCtrl'
        }
      }
    })
	.state('tab.map-month', {
      url: '/map/period/:period',
      views: {
        'tab-map': {
          templateUrl: 'partials/event-map.html',
          controller: 'EventListCtrl'
        }
      }
    })	
;

  // if none of the above states are matched, use this as the fallback
  $urlRouterProvider.otherwise('/tab/dash');

});


var utils = {

	
};

month_names = [
					'January',
					'February',
					'March',
					'April',
					'May',
					'June',
					'July',
					'August',
					'September',
					'October',
					'November',
					'December'
				];

Date.prototype.getMonthName = function(){
	return month_names[this.getMonth()];
}

function getMonthNumber(monthName){
console.log("getMonthNumber:"+monthName+":");
//console.debug(month_names.indexOf['October'));
	
	return month_names.indexOf(monthName);
}

Date.prototype.getDayName = function(){
	var days_full = [
						'Sunday',
						'Monday',
						'Tuesday',
						'Wednesday',
						'Thursday',
						'Friday',
						'Saturday'
					];
	return days_full[this.getDay()];
};

Date.prototype.getMonthNameShort = function(){
	var month_names_short = [
						'Jan',
						'Feb',
						'Mar',
						'Apr',
						'May',
						'Jun',
						'Jul',
						'Aug',
						'Sep',
						'Oct',
						'Nov',
						'Dec'
					];

	return month_names_short[this.getMonth()];
}

Date.prototype.getDayNameShort = function(){
	var days_short = [
						'Sun',
						'Mon',
						'Tue',
						'Wed',
						'Thu',
						'Fri',
						'Sat'
					];
	return days_short[this.getDay()];
};

/*
running_events
	- running_event_pk
	- eventdate
	- title
	- city
	- url_website
	- distances
	- description
	- url_inschrijven_nl
	- url_mylaps
 
 
 */
 