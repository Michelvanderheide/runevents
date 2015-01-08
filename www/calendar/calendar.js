clientId = '420520060585-f6nd8f8ol4b76rgknrfpn8furricur6q.apps.googleusercontent.com';
apiKey = 'AIzaSyCUdm8rO-3FlHMUoyrhO79n6Ay6CnTeqbQ';
scopes = 'https://www.googleapis.com/auth/calendar';

/*

https://www.googleapis.com/calendar/v3/calendars/michelvanderheide%40gmail.com/events?key=AIzaSyCUdm8rO-3FlHMUoyrhO79n6Ay6CnTeqbQ

https://accounts.google.com/o/oauth2/auth?client_id=420520060585-f6nd8f8ol4b76rgknrfpn8furricur6q
&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fcalendar
&immediate=false
&include_granted_scopes=true
&proxy=oauth2relay230507460&redirect_uri=postmessage
&origin=http%3A%2F%2Fwww.avgoor.nl
&response_type=token
&state=353470762%7C0.3860813074
&authuser=0

https://accounts.google.com/o/oauth2/auth?client_id=420520060585-f6nd8f8ol4b76rgknrfpn8furricur6q.apps.googleusercontent.com&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fcalendar&immediate=false&include_granted_scopes=true&proxy=oauth2relay608851919&redirect_uri=postmessage&origin=http%3A%2F%2Fwww.avgoor.nl&response_type=token&state=364853239%7C0.4197814883&authuser=0


// Add an event to calendar of michelvanderheide@gmail.com

PUT https://www.googleapis.com/calendar/v3/calendars/michelvanderheide%40gmail.com/events/n9ls3o6hvtrb7our8sn5gcltoo?alwaysIncludeEmail=true&sendNotifications=true&key={YOUR_API_KEY}

Content-Type:  application/json
Authorization:  Bearer ya29.1.AADtN_XNvD1ZSDXpFetXBoiAI1xUAkxuBnzqO7XXswBRdI16tT0s0Mo48YAZK0u1kajNkUUsC7pUFdytuebVJJE
X-JavaScript-User-Agent:  Google APIs Explorer
 
{
 "end": {
  "date": "2014-08-04"
 },
 "start": {
  "date": "2014-08-04"
 },
 "summary": "Verjaardag"
}
*/

function handleClientLoad() {
  console.log("handleClientLoad");
  gapi.client.setApiKey(apiKey);
  //window.setTimeout(checkAuth,1);
  //checkAuth();
}

function checkAuth() {
  console.log("checkAuth:"+clientId);
  gapi.auth.authorize({client_id: clientId, scope: scopes, immediate: true},
      handleAuthResult);
}

function handleAuthResult(authResult) {
  //console.log("handleAuthResult:"+authResult);
  var authorizeButton = document.getElementById('authorize-button');
  if (authResult) {
    authorizeButton.style.visibility = 'hidden';
    getCalenderList();
  } else {
    authorizeButton.style.visibility = '';
    authorizeButton.onclick = handleAuthClick;
   }
}

function handleAuthClick(event) {
  console.log("handleAuthClick");
  gapi.auth.authorize(
      {client_id: clientId, scope: scopes, immediate: false},
      handleAuthResult);
  return false;
}

function makeApiCall() {
  console.log("makeApiCall");
  gapi.client.load('calendar', 'v3', function() {
    var request = gapi.client.calendar.events.list({
      'calendarId': 'jolandevanderheide@gmail.com'
    });
          
    request.execute(function(resp) {
      for (var i = 0; i < resp.items.length; i++) {
        var li = document.createElement('li');
        li.appendChild(document.createTextNode(resp.items[i].summary));
        document.getElementById('events').appendChild(li);
      }
    });
  });
}

function createCalendarEventClick(event) {
  console.log("handleAuthClick");
  gapi.auth.authorize(
      {client_id: clientId, scope: scopes, immediate: true},
      handleAuthResult);
  return false;
}

function createCalendarEvent() {
  console.log("createCalendarEvent");
  gapi.client.load('calendar', 'v3', function() {
	var resource = {
		"summary": "Wedstrijdje",
		"location": "Goor",
		"start": {
		"date": "2014-05-01"
		},
		"end": {
		"date": "2014-05-01"
		}
	};

	var request = gapi.client.calendar.events.insert({
		'calendarId': 'michelvanderheide@gmail.com',
		'resource': resource
	});  
console.log("Request");
console.log(request);
    request.execute(function(resp) {
		console.log(resp);
		console.log("done")
    });
  });
}

function getCalenderList() {

//minAccessRole
  console.log("getCalenderList");
  gapi.client.load('calendar', 'v3', function() {
    var request = gapi.client.calendar.calendarList.list({
      'minAccessRole': 'owner'
	  
    });
          
    request.execute(function(resp) {
      for (var i = 0; i < resp.items.length; i++) {
        var li = document.createElement('li');
        li.appendChild(document.createTextNode(resp.items[i].summary));
        document.getElementById('events').appendChild(li);
      }
    });
  });
}

