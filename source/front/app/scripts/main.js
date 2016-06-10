/*! handmade by esmes.fi */

//Linter preferences
/*global $, FastClick, moment, sweetAlert, NProgress*/
/*jslint node: true*/
/*jshint unused:false, camelcase:false */

// ### Settings and such

//Fixes some browsers history
window.onunload = function(){};

//Returns size of an object
Object.size = function(obj) {
  'use strict';
  var size = 0, key;
  for (key in obj) {
    if (obj.hasOwnProperty(key)) {
      size++;
    }
  }
  return size;
};

//Validators
var emailCheck = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
var phoneCheck = /^[0-9 \-+()]+$/;

//Debug setting
var debug = true;

//Configuration object
var config = {
  appUrl: null,
  profileUrl: 'https://www.mazhr.com/profile/',
  //backendUrl: '//testipenkki.esmes.fi/mzr/back',
  backendUrl: '//api.mazhr.dev',
  apiPath: 'api/v1',
  datastore: 'mahzr_app_v1_settings',
  publicViews: ['#start', '#login', '#signup', '#tryout'],
  allowedViews: ['#frontpage', '#profile', '#tests', '#settings'],
  confirmButtonColor: '#00b0a0',
  destructiveButtonColor: '#c44040'
};

//IE Detect
function getInternetExplorerVersion(){
  'use strict';
  var rv = -1; // Return value assumes no IE
  if (navigator.appName === 'Microsoft Internet Explorer') {
    var ua = navigator.userAgent;
    var re = new RegExp('MSIE ([0-9]{1,}[.0-9]{0,})');
    if (re.exec(ua) !== null) {
      rv = parseFloat( RegExp.$1 );
    }
  }
  return rv;
}

//Handlebars helpers
window.Handlebars.registerHelper('select', function(value, options){
  'use strict';
  var el = $('<select />').html(options.fn(this));
  el.find('[value="'+value+'"]').attr({'selected':'selected'});
  return el.html();
});

window.Handlebars.registerHelper('checkArray', function(values, options){
  'use strict';
  var el = $('<div />').html(options.fn(this));
  for(var i=0;i<values.length;i++){
    el.find('input[name='+values[i].id+']').attr({'checked':'checked'});
  }
  return el.html();
});

window.Handlebars.registerHelper('checkRadio', function(values, options){
  'use strict';
  var el = $('<div />').html(options.fn(this));
  for(var i=0;i<values.length;i++){
    el.find('input[value='+values[i].id+']').attr({'checked':'checked'});
  }
  return el.html();
});

window.Handlebars.registerHelper('ifNotEmpty', function(value, options) {
  'use strict';
  if(value !== undefined && value !== '' && value.length !== 0) {
    return options.fn(this);
  }
});

// Convert base64/URLEncoded data component to raw binary data held in a string
function dataURItoBlob(dataURI) {
  'use strict';
  var byteString = atob(dataURI.split(',')[1]);
  var ab = new ArrayBuffer(byteString.length);
  var ia = new Uint8Array(ab);
  for (var i = 0; i < byteString.length; i++) {
    ia[i] = byteString.charCodeAt(i);
  }
  return new Blob([ab], { type: 'image/jpeg' });
}

String.prototype.capitalizeFirstLetter = function() {
  'use strict';
  return this.charAt(0).toUpperCase() + this.slice(1);
};

//Get current url, do not include hashtags
function getCurrentUrl(){
  'use strict';
  var url = window.location.href;
  if(url.indexOf('#') !== -1){
    url = url.substr(0, url.indexOf('#'));
  }
  return url;
}

//Checks for instrument_id in tests, if matches, adds to sorted
function checkAndAdd(id, tests, sorted) {
  'use strict';
  for(var i=0; i<tests.length; i++){
    var test = tests[i];
    if(test.instrument_id === id){
      sorted.push(test);
      return true;
    }
  }
  return false;
}

//Shuffle array, Fisher-Yates (aka Knuth) Shuffle
function shuffle(array){
  'use strict';
  var currentIndex = array.length, temporaryValue, randomIndex ;
  while (0 !== currentIndex) {
    randomIndex = Math.floor(Math.random() * currentIndex);
    currentIndex -= 1;
    temporaryValue = array[currentIndex];
    array[currentIndex] = array[randomIndex];
    array[randomIndex] = temporaryValue;
  }
  return array;
}

//Log output if debug is true
function log(obj){
  'use strict';
  if (debug && console !== undefined && console.log !== undefined) {
    console.log(obj);
  }
}

//Renders stars
function getStars(score){
  'use strict';
  var result = '';
  var stars = 0;
  for(var i=1; i<=5; i++){
    if (i <= score) {
      stars++;
      result += '<span class="star fullstar"></span>';
    } else {
      if(score % 1 !== 0){
        stars++;
        result += '<span class="star halfstar"></span>';
      }
      break;
    }
  }
  while(stars < 5){
    result += '<span class="star emptystar"></span>';
    stars++;
  }
  return result;
}

//Converts score and renders stars
function getStarsFromScore(original_score){
  'use strict';
  original_score = parseInt(original_score, 10);
  var score = (original_score+1)*0.5;
  return getStars(score);
}

//Save stuff to localStorage
function saveData(key, data){
  'use strict';
  if (localStorage !== undefined) {
    localStorage.setItem(key, JSON.stringify(data));
    //log('Saved key: '+key+' to localStorage.');
  } else {
    //log('ERROR: localStorage NOT DEFINED!');
  }
}

//Clear data from localStorage
function clearData(key){
  'use strict';
  if (localStorage !== undefined) {
    //log('Clearing ['+key+'] from localStorage');
    localStorage.removeItem(key);
  } else {
    //log('ERROR: localStorage NOT DEFINED!');
  }
}

//Load stuff from localStorage
function loadData(key){
  'use strict';
  if (localStorage !== undefined) {
    //log('Loading ['+key+'] from localStorage');
    if (localStorage.getItem(key) === null) {
      return null;
    }
    return JSON.parse(localStorage.getItem(key));
  } else {
    //log('ERROR: localStorage NOT DEFINED!');
    return null;
  }
}

//Advance fullscreen register form
function fullscreenFormNext(form){
  'use strict';
  var current = $(form).find('fieldset.active').eq(0);

  $('.fullscreenform fieldset.error').removeClass('error');

  var next = $(current).next();

  if(next.length > 0){

    //Validate
    var fields = $(current).find('input, select');
    $.each(fields, function(i, field){
      //log($(field).attr('name')+': '+$(field).val());

      if($(field).attr('data-type') === 'email' && ($(field).val() === '' || !emailCheck.test($(field).val())) ){
        $(field).closest('fieldset').addClass('error');
      } else if ($(field).attr('data-type') === 'password' && ($(field).val() === '' || $(field).val().length < 6 )) {
        $(field).closest('fieldset').addClass('error');
      } else if ($(field).attr('data-type') === 'select' && $(field).val() === '') {
        $(field).closest('fieldset').addClass('error');
      } else if ($(field).val() === '') {
        $(field).closest('fieldset').addClass('error');
      }

    });

  }

  if ($('.fullscreenform fieldset.error').length > 0){
    //log('Validation error');

  } else {
    //log('Next!');
    $(current).addClass('fading-out').removeClass('active');
    window.setTimeout(function(){ $(current).removeClass('fading-out'); }, 1000);
    $(next).addClass('active');
    $(next).find('input').focus();

    $(form).find('.formcontroller').removeClass('active');

    var number = next.data('field');
    var controller = $(form).find('.formcontroller[data-field="'+number+'"]');
    $(controller).addClass('enabled active');

  }

}

//Go to specific phase on fullscreen form
function fullscreenFormGoTo(form, number){
  'use strict';
  var current = $(form).find('fieldset.active').eq(0);

  $('.fullscreenform fieldset.error').removeClass('error');

  var next = $(form).find('fieldset[data-field="'+number+'"]');

  if( parseInt($(next).data('field'), 10) > parseInt($(current).data('field'), 10) ){

    //Validate
    var fields = $(current).find('input, select');
    $.each(fields, function(i, field){
      //log($(field).attr('name')+': '+$(field).val());

      if($(field).attr('data-type') === 'email' && ($(field).val() === '' || !emailCheck.test($(field).val())) ){
        $(field).closest('fieldset').addClass('error');
      } else if ($(field).attr('data-type') === 'select' && $(field).val() === '') {
        $(field).closest('fieldset').addClass('error');
      } else if ($(field).val() === '') {
        $(field).closest('fieldset').addClass('error');
      }

    });

  }

  if ($('.fullscreenform fieldset.error').length > 0){
    //log('Validation error');

  } else {
    //log('Next!');
    $(current).addClass('fading-out').removeClass('active');
    window.setTimeout(function(){ $(current).removeClass('fading-out'); }, 1000);
    $(next).addClass('active');
    $(next).find('input').focus();

    $(form).find('.formcontroller').removeClass('active');

    var controller = $(form).find('.formcontroller[data-field="'+number+'"]');
    $(controller).addClass('enabled active');

  }
}

//Load settings from localstorage
var settings = loadData(config.datastore);
if(settings === null){
  settings = {
    language: 'fi_fi'
  };
}

//Main App object
function App(){
  'use strict';
  var a = this; //Easier to type, and we need a reference other than 'this' inside xhr callbacks.

  //Settings
  a.currentLanguage = settings.language ? settings.language : 'fi_fi';
  a.translations = {};

  //User object
  a.user = {'loggedIn': false, 'data': {}};

  //Random variables
  a.lockTagit = false;
  a.view = null;
  a.showLoader = false;

  a.matchOpen = false;
  a.matchArea = false;
  a.matchKeywords = '';

  a.linkedinData = false;

  a.filterAjax = false;
  a.possibilityAjax = false;
  a.matchAjax = false;

  a.imageCropperEnabled = true;

  //Loader spinner
  a.loading = {
    start: function(){
      if(a.showLoader){
        NProgress.start();
      }
    },
    trickle: function(){
      NProgress.inc();
    },
    stop: function(){
      NProgress.done();
    }
  };

  //Methods

  //Loads translation
  a.loadTranslation = function(locale, filename){
    var dfd = new $.Deferred();
    $.get(filename, function(data){
      if(data !== undefined) {
        //log('Loaded translations for '+locale+' from '+filename);
        a.translations[locale] = data;
        dfd.resolve('OK');
      }
    }, 'json');
    return dfd.promise();
  };

  //Scroll viewport to top
  a.scrollUp = function(){
    $('html,body').animate({ scrollTop: 0 }, 200);
  };

  //Get parsed data from a job
  a.getParsedData = function(job){
    var l = a.translations[a.currentLanguage].job.fields;

    var data = [];

    function p(field, val){
      if(val !== undefined) {
        //Strip html.
        data.push({field: field, value: val.replace(/(<([^>]+)>)/ig,'')});
      }
    }

    p(l.contact, job.json_ad.yhteystiedotKevytHTML);
    p(l.job_pay, job.json_ad.palkkausteksti);
    p(l.job_experience, job.json_ad.tyokokemus);
    p(l.job_starts, job.json_ad.tyoAlkaaTekstiYhdistetty);
    p(l.job_time, job.json_ad.tyoaikatekstiYhdistetty);
    p(l.duration, job.json_ad.tyonKesto);
    p(l.search_ends, job.json_ad.hakuPaattyy);
    p(l.job_published, moment(job.json_ad.ilmoituspaivamaara).format('L'));

    return data;

  };

  //Makes an api url
  a.getApiUrl = function(endpoint){
    var apiUrl = config.backendUrl + '/' + config.apiPath + '/';
    apiUrl += endpoint;
    return apiUrl;
  };

  //All api calls goes through this function
  a.apiCall = function(endpoint, data, success, failure, type, sendRaw){
    var headers = {};

    if(settings.apiToken !== undefined){
      headers.Authorization = 'Bearer ' + settings.apiToken;
    }

    if(settings.tempToken !== undefined){
      headers.MazhrSession = settings.tempToken;
    }

    var ajax_settings = {
      type: type,
      headers: headers,
      dataType: 'JSON',
      data: data
    };

    if(sendRaw !== undefined && sendRaw === true){
      ajax_settings.processData = false;
      ajax_settings.contentType = false;
    }

    var jqXHR = $.ajax(a.getApiUrl(endpoint), ajax_settings).done(function(response){
      success(response);
    }).fail(function(jqXHR, textStatus, errorThrown) {
      failure(jqXHR, textStatus, errorThrown);
    });
    return jqXHR;
  };

  //Fetches linkedin data again
  a.fetchLinkedin = function(){
    a.loading.start();
    var profession = a.user.data.filters.profession_code.value;
    var area = a.user.data.filters.area.value;
    window.location = config.backendUrl + '/linkedin?mazhr_token=' + settings.tempToken + '&what=' + profession + '&where=' + area+ '&ok_url=' + encodeURIComponent(config.appUrl + '#settings') + '&fail_url=' + encodeURIComponent(config.appUrl + '#settings');
  };

  //Changes email address
  a.changeEmail = function(){
    var email = $('#emailfield').val();
    //log(email);
    if(email !== '' && emailCheck.test(email)){
      //Email ok

      var jqXHR = a.apiCall('me', JSON.stringify({
        'data': {
          'email': email
        }
      }), function(response){
        //Success
        a.message('email_success');
        a.redrawAll();
      }, function(){
        //Failure
        a.message('email_error');
      }, 'POST');

    } else {
      a.message('email_incorrect');
    }
  };

  //Requests a password reset
  a.forgotPassword = function(){

    var email = $('#forgot-password').val();
    //log(email);

    if(email !== '' && emailCheck.test(email)){
      //Email ok

      $.ajax(a.getApiUrl('password'), {
        type:'post',
        dataType : 'JSON',
        data: {'email': email},
      }).done(function(){
        a.message('forgot_success');
        $('#form-forgot-password').slideUp();
        $('#forgot-password').val('');
      }).fail(function(response) {
        a.message('email_incorrect');
      });

    } else {
      a.message('email_incorrect');
    }


  };

  //Change password
  a.changePassword = function(){
    if($('#password_1').val().length < 6 || $('#password_1').val() !== $('#password_2').val()){
      a.message('password_incorrect');
    } else {
      //Password ok

      var jqXHR = a.apiCall('me/password', {'password': $('#password_1').val()}, function(response){
        //Success
        a.message('password_success');
        a.redrawAll();
      }, function(){
        //Failure
        a.message('password_error');
      }, 'POST');

    }
  };

  //Saves privacy settings
  a.savePrivacy = function(){

    //Init data
    var data = {
      'public_profile': 0,
      'show': ''
    };

    //Get data
    if($('#privacy-public').is(':checked')){
      data.public_profile = 1;

      $.each($('#privacy-checkboxes input[type="checkbox"]:checked'), function(i, box){
        data.show = data.show + ',' + $(box).attr('name');
      });

    }

    //Remove first ','
    if(data.show !== '') {
      data.show = data.show.slice(1);
    }

    //Call
    var jqXHR = a.apiCall('me/privacy', JSON.stringify({
      'data': data
    }), function(response){
      //Success
      a.user.data.privacy = response.data;
      a.message('privacy_success');
      a.redrawAll(true);
    }, function(){
      //Failure
      a.message('privacy_error');
    }, 'POST');

  };

  //Removes account
  a.removeAccount = function(){
    var messages = a.translations[a.currentLanguage].alerts.messages;

    sweetAlert({
      title: messages.profile_delete_confirm_1.title,
      text: messages.profile_delete_confirm_1.message,
      type: messages.profile_delete_confirm_1.type,
      showCancelButton: true,
      confirmButtonColor: config.destructiveButtonColor,
      confirmButtonText: messages.profile_delete_confirm_1.button_ok,
      cancelButtonText: messages.profile_delete_confirm_1.button_cancel,
      closeOnConfirm: false
    }, function(){

      sweetAlert({
        title: messages.profile_delete_confirm_2.title,
        text: messages.profile_delete_confirm_2.message,
        type: messages.profile_delete_confirm_2.type,
        showCancelButton: true,
        confirmButtonColor: config.destructiveButtonColor,
        confirmButtonText: messages.profile_delete_confirm_2.button_ok,
        cancelButtonText: messages.profile_delete_confirm_2.button_cancel,
        closeOnConfirm: true,
        html: true
      }, function(){

        var jqXHR = a.apiCall('me/remove', {}, function(response){
          //Success
          a.message('remove_success');
          a.logout();
        }, function(){
          //Failure
          a.message('remove_error');
        }, 'POST');

      });

    });
  };

  //Checks for valid login
  a.loginCheck = function(success, failure){

    //delete settings.apiToken;
    //delete settings.tempToken;
    //saveData(config.datastore, settings);

    var jqXHR = a.apiCall('me', {}, function(response){
      //Success

      if(response.metadata.tmpToken !== undefined){
        //log('tempToken was set');
        settings.tempToken = response.metadata.tmpToken;
        saveData(config.datastore, settings);
        //log(settings);

        a.checkMessages();

      } else {
        a.logout();
      }

      a.user.loggedIn = true;
      a.user.data = response.data;
      a.user.metadata = response.metadata;
      success(response);

    }, function(response){
      //Failure

      //log(response);

      var responseData = JSON.parse(response.responseText);

      delete settings.apiToken;

      if(responseData.tmpToken !== undefined){
        //log('tempToken was set');
        settings.tempToken = responseData.tmpToken;
      }

      saveData(config.datastore, settings);
      //log(settings);

      a.checkMessages();

      if(settings.tempToken !== undefined){

        a.apiCall('register', {}, function(response){

          //log(response);

          if(response.metadata.mazhr_token !== undefined){
            //log('Got token, logging in!');
            settings.apiToken = response.metadata.mazhr_token;
            saveData(config.datastore, settings);
            //log(settings);

            a.user.loggedIn = true;
            a.user.data = response.data;
            a.user.metadata = response.metadata;
            success(response);

          } else {
            //log('No token.');
            delete settings.apiToken;
            saveData(config.datastore, settings);

            if(response.metadata.linkedinData !== undefined){
              //log('Got linkedIn data');
              a.linkedinData = response.metadata.linkedinData;
              //log(a.linkedinData);
            }

            a.user.loggedIn = false;
            a.user.data = {};
            a.user.metadata = {};
            failure();
          }

        }, function(response){
          delete settings.apiToken;
          saveData(config.datastore, settings);

          a.user.loggedIn = false;
          a.user.data = {};
          a.user.metadata = {};
          failure();
        }, 'POST');

      } else {
        a.user.loggedIn = false;
        a.user.data = {};
        a.user.metadata = {};
        failure();
      }


    }, 'GET');
    return jqXHR;

  };

  //Checks for messages from the backend on login
  a.checkMessages = function(){
    var jqXHR = a.apiCall('messages', {}, function(data){
      //Success
      if(data.message !== '') {
        a.message(data.message);
      }
    }, function(){
      //Failure
    }, 'GET');
    return jqXHR;
  };

  //Updates/shows the test progress bubble on tests menu button
  a.updateTestProgress = function(){
    //log('a.updateTestProgress');

    var data = a.user.metadata;

    //log(data);

    if(data !== undefined && data.tests !== undefined && data.testsDone !== undefined){
      var lang = a.translations[a.currentLanguage];

      var tests_left = parseInt(data.tests,10) - parseInt(data.testsDone, 10);

      if (tests_left === 0) {
        if ($('.pageselector[data-page="tests"] .notifier').length > 0) {
          $('.pageselector[data-page="tests"] .notifier').remove();
        }
      } else {
        if ($('.pageselector[data-page="tests"] .notifier').length === 0) {
          $('.pageselector[data-page="tests"]').append('<div class="notifier" title="'+lang.tests_left+'">'+tests_left+'</div>');
        } else {
          $('.pageselector[data-page="tests"]').find('.notifier').html(tests_left);
        }
      }

    } else {

      a.apiCall('me', {}, function(response){
        a.user.data = response.data;
        a.user.metadata = response.metadata;
        a.updateTestProgress();
      }, function(){
        a.message('generic_error');
      }, 'GET');

    }

  };

  //Refreshes the temp token
  a.refreshTempToken = function(success, failure){
    var jqXHR = a.apiCall('me', {}, function(response){
      //Success
      if(response.metadata.tmpToken !== undefined){
        //log('tempToken was set');
        settings.tempToken = response.metadata.tmpToken;
        saveData(config.datastore, settings);
        //log(settings);
        success();
      } else {
        failure();
      }

    }, function(){
      //Failure
      failure();
    }, 'GET');
    return jqXHR;
  };

  //Show a message
  a.message = function(message_id){

    var lang = a.translations[a.currentLanguage];

    var message = lang.alerts.messages[message_id];
    var title = message.title;
    var text = message.message;
    var type = message.type;

    sweetAlert({
      title: title,
      text: text,
      type: type,
      confirmButtonText: lang.alerts.messages.ok,
      confirmButtonColor: config.confirmButtonColor
    });

  };

  //Hash change function. Used to navigate
  a.hashChanged = function(){
    if(window.location.hash.split('-')[0] === '#job' ){
      //log(window.location.hash.split('-')[1]);
      return false;
    } else if(window.location.hash !== App.view) {
      a.showView(window.location.hash);
    }
  };

  //Change language. TODO
  a.changeLanguage = function(locale){
    if(a.translations[locale] !== undefined){
      //log('Language changed');
      a.currentLanguage = locale;

      //Set moment language
      moment.locale(a.translations[a.currentLanguage].moment_lang);

      //Reset views
      a.matchOpen = false;
      a.matchArea = false;
      a.matchKeywords = '';

      //a.redrawAll();

      $.when(
        $('#app').fadeOut(300)
      ).then(function(){
        a.redrawAll();
        $('#app').delay(200).fadeIn(300);
      });

      settings.language = locale;
      saveData(config.datastore, settings);
    } else {
      //log('ERROR: Language not supported!');
    }
  };

  //Used to render handlebars template with data, then appended if wanted
  a.render = function(templateId, data, target, append){
    if(target === undefined) {
      target = '#content-page';
    }
    if(append === undefined) {
      append = false;
    }
    var source = $(templateId).html();
    var template = window.Handlebars.compile(source);
    var html = template(data);

    if(append){
      $(target).append(html);
    } else {
      $(target).html(html);
    }

  };

  //Redraws the view
  a.redrawAll = function(skipLeaveCheck){
    a.showView(a.view, true, skipLeaveCheck);
  };

  //Parses position duration, keywords...
  a.parsePosition = function(i, pos){
    var data = {lang: a.translations[a.currentLanguage]};

    var start_pretty = pos.start_month+'/'+pos.start_year;
    var end_pretty, duration_pretty, duration;
    if(pos.current === 1) {
      end_pretty = data.lang.profile.time_current;
      //duration_pretty = moment.duration(moment(start_pretty, 'M/YYYY').diff(moment())).humanize(true);
      duration = moment.duration(moment().diff(moment(start_pretty, 'M/YYYY')));
      pos.not_ended = true;
    } else {
      end_pretty = pos.end_month+'/'+pos.end_year;
      //duration_pretty = moment.duration(moment(start_pretty, 'M/YYYY').diff(moment(end_pretty, 'M/YYYY'))).humanize(true);
      duration = moment.duration(moment(end_pretty, 'M/YYYY').diff(moment(start_pretty, 'M/YYYY')));
    }

    if(duration.asYears() !== 0){
      duration_pretty = Math.round(duration.asYears());
      if(duration_pretty === 1){
        duration_pretty += ' ' + data.lang.date_year_single;
      } else {
        duration_pretty += ' ' + data.lang.date_years;
      }
    } else {
      duration_pretty = Math.round(duration.asMonths());
      if(duration_pretty === 1){
        duration_pretty += ' ' + data.lang.date_month_single;
      } else {
        duration_pretty += ' ' + data.lang.date_months;
      }
    }

    pos.start_pretty = start_pretty;
    pos.end_pretty = end_pretty;
    pos.duration_pretty = duration_pretty;

    pos.end_month = pos.end_month === null ? '' : pos.end_month;
    pos.end_year = pos.end_year === null ? '' : pos.end_year;

    pos.keywords_array = (pos.keywords !== null && pos.keywords !== '') ? pos.keywords.split(',') : [];
  };

  //Parses education
  a.parseEducation = function(i, pos){
    var data = {lang: a.translations[a.currentLanguage]};

    var duration_pretty;

    if(pos.current === 1) {
      duration_pretty = data.lang.profile.form.education_not_ended;
      pos.not_ended = true;
    } else {
      duration_pretty = data.lang.profile.form.end_year+' '+pos.end_year;
    }

    pos.duration_pretty = duration_pretty;

    pos.keywords_array = (pos.keywords !== null && pos.keywords !== '') ? pos.keywords.split(',') : [];

  };

  //Gets tests from backend, parses them, render
  a.getTests = function(){
    //log('getTests');
    var jqXHR = a.apiCall('tests', {}, function(response){
      //Success

      var data = {lang: a.translations[a.currentLanguage]};
      data.tests = response.data;

      if(a.user.data.tests.length) {
        $.each(a.user.data.tests, function(key, value){
          if(data.tests[value.instrument_id] !== undefined){
            data.tests[value.instrument_id].content = value;

            var formatted_score;
            var test_name = data.tests[value.instrument_id].name;
            var score = data.tests[value.instrument_id].content.score + '';

            //Format score
            if(score.length === 1 && data.tests[value.instrument_id].content.score_key !== '' && data.tests[value.instrument_id].content.score_key !== null){
              var diagramdata = {
                value: score,
                text: {
                  title: data.lang.tests.testdata[test_name].name,
                  description: data.lang.tests.result_texts[score]
                }
              };
              var template = window.Handlebars.compile($('#diagram-template').html());
              var html = template(diagramdata);
              formatted_score = html;
            } else {
              formatted_score = '<p class="scoretext">'+data.tests[value.instrument_id].content.score+'</p>';
            }

            var paid = false;
            if(data.tests[value.instrument_id].content.status === 1){
              paid = true;
            }

            var payment_pending = false;
            if(data.tests[value.instrument_id].content.status === 3){
              payment_pending = true;
            }

            data.tests[value.instrument_id].content.formatted_score = formatted_score;
            data.tests[value.instrument_id].paid = paid;
            data.tests[value.instrument_id].payment_pending = payment_pending;

          }

        });
      }

      $.each(data.tests, function(i, test){
        test.replaced_price = test.price.replace('.', ',');
        test.text = data.lang.tests.testdata[test.name];

        if(parseInt(test.price, 10) === 0) {
          //Test is free
          test.text.buy = data.lang.tests.claim_test;
          test.not_free = false;

        } else {
          //Test is not free
          test.text.buy = data.lang.tests.buy_test;
          test.not_free = true;

        }

      });

      data.backendUrl = config.backendUrl;
      data.appUrl = config.appUrl;

      /* Sort tests */
      var order = [102, 201, 302, 301, 357, 315];

      var sorted_done = {};
      var sorted_not_done = {};

      var final_order = {};

      //Sort based on order array
      $.each(order, function(i, value){
        if(data.tests[value] !== undefined){
          //log(data.tests[value]);
          if(data.tests[value].content !== undefined && data.tests[value].content.score_key !== null){
            //done
            sorted_done['test_'+value] = data.tests[value];
          } else {
            sorted_not_done['test_'+value] = data.tests[value];
          }
          delete data.tests[value];
        }
      });

      //Add rest, which are not in order array
      $.each(data.tests, function(key, value){
        sorted_not_done[key] = value;
      });

      //Add 102 first, if found...
      if(sorted_done['test_'+102] !== undefined){
        final_order['test_'+102] = sorted_done['test_'+102];
        delete sorted_done['test_'+102];
      } else if(sorted_not_done['test_'+102] !== undefined){
        final_order['test_'+102] = sorted_not_done['test_'+102];
        delete sorted_not_done['test_'+102];
      }

      //Add done tests first
      $.each(sorted_done, function(key, value){
        final_order[key] = value;
      });

      //Then add not done tests
      $.each(sorted_not_done, function(key, value){
        final_order[key] = value;
      });

      data.tests = final_order;

      /* --------- */

      //log(data);

      a.render('#content-page-tests-template', data, '#content-page');

      $.each($('#page-tests .custom-select'), function(i, obj){
        $(obj).find('select').off().on('change', function(){
          //log('Select changed!');
          $(this).closest('.custom-select').find('label').html($(this).find('option:selected').text());
        });
      });

      //Open tests
      $('.test[data-test="shapes"]').addClass('open');
      $('.test[data-test="shapes"]').find('.info').css({display: 'block'});

      //log(settings.openTests);
      //Check if open tests are saved, and open them
      if(settings.openTests !== undefined){
        $.each(settings.openTests, function(i, testname){
          //log('Open test '+testname);
          var test = $('.test[data-test="'+testname+'"]');
          $(test).addClass('open');
          $(test).find('.info').css({display: 'block'});
        });
        delete settings.openTests;
        saveData(config.datastore, settings);
      }

    }, function(){
      //Failure
      //log('getTests failed');
    }, 'GET');
    return jqXHR;
  };

  //Starts a test
  a.startTest = function(instrument, test, language){
    a.loading.start();
    a.saveOpenTests();
    var url = config.backendUrl + '/cute/test?mazhr_token=' + settings.tempToken +  '&instrument=' + instrument + '&testid=' + test + '&lang=' + language + '&return_url=' + config.appUrl + 'loading/' + '&fail_url=' + encodeURIComponent(config.appUrl + '#tests');
    window.location = url;
  };

  //Reset payment
  a.resetPayment = function(testid){
    //log('resetPayment');
    var messages = a.translations[a.currentLanguage].alerts.messages;

    sweetAlert({
      title: messages.test_resetpayment_confirm.title,
      text: messages.test_resetpayment_confirm.message,
      type: messages.test_resetpayment_confirm.type,
      showCancelButton: true,
      confirmButtonColor: config.destructiveButtonColor,
      confirmButtonText: messages.test_resetpayment_confirm.button_ok,
      cancelButtonText: messages.test_resetpayment_confirm.button_cancel,
      closeOnConfirm: false
    }, function(){

      a.loading.start();

      a.saveOpenTests();

      var jqXHR = a.apiCall('test/reset/'+testid, {}, function(response){
        //Success
        a.user.data.tests = response.data;

        $.when(a.getTests()).then(function(){

          sweetAlert({
            confirmButtonColor: config.confirmButtonColor,
            title: messages.test_resetpayment_success.title,
            text: messages.test_resetpayment_success.message,
            type: messages.test_resetpayment_success.type
          });

          a.loading.stop();

        });

      }, function(){
        //Failure
        a.message('generic_error');
        a.loading.stop();
      }, 'POST');
    });
  };

  //Check if editor is editing when leaving. Display confirm.
  a.onLeaveCheck = function(skipLeaveCheck, confirm, cancel){
    //log('onLeaveCheck');

    if(skipLeaveCheck !== undefined && skipLeaveCheck === true){
      confirm();
    } else {

      if($('.editor.editing').length > 0){
        var messages = a.translations[a.currentLanguage].alerts.messages;

        sweetAlert({
          title: messages.profile_leave_confirm.title,
          text: messages.profile_leave_confirm.message,
          type: messages.profile_leave_confirm.type,
          showCancelButton: true,
          confirmButtonColor: config.destructiveButtonColor,
          confirmButtonText: messages.profile_leave_confirm.button_ok,
          cancelButtonText: messages.profile_leave_confirm.button_cancel,
          closeOnConfirm: true
        }, function(isConfirmed){
          if(isConfirmed){
            confirm();
          } else {
            cancel();
          }
        });

      } else {
        confirm();
      }
    }

  };

  //Resets test, if confirmed in dialog
  a.resetTest = function(testid){
    //log('resetTest');
    var messages = a.translations[a.currentLanguage].alerts.messages;

    sweetAlert({
      title: messages.test_reset_confirm.title,
      text: messages.test_reset_confirm.message,
      type: messages.test_reset_confirm.type,
      showCancelButton: true,
      confirmButtonColor: config.destructiveButtonColor,
      confirmButtonText: messages.test_reset_confirm.button_ok,
      cancelButtonText: messages.test_reset_confirm.button_cancel,
      closeOnConfirm: false
    }, function(){

      a.loading.start();

      var jqXHR = a.apiCall('test/reset/'+testid, {}, function(response){
        //Success
        a.user.data.tests = response.data;
        a.user.metadata = response.metadata;
        a.updateTestProgress();

        $.when(a.getTests()).then(function(){

          sweetAlert({
            confirmButtonColor: config.confirmButtonColor,
            title: messages.test_reset_success.title,
            text: messages.test_reset_success.message,
            type: messages.test_reset_success.type
          });

          a.loading.stop();

        });

      }, function(){
        //Failure
        a.message('generic_error');
        a.loading.stop();
      }, 'POST');
    });

  };

  //Gets matches
  a.getMatches = function(){
    //log('getMatches');

    if(a.matchAjax !== false){
      //log('Aborting old query.');
      a.matchAjax.abort();
      a.matchAjax = false;
    }

    var data = {lang: a.translations[a.currentLanguage]};

    $.each(a.user.data.tests, function(index, test){
      if(test.instrument_id === 102 && test.score !== null && test.score_key !== null) {
        //log('This guy is tested!');
        data.tested = true;
      }
    });

    var jqXHR = a.apiCall('match/profiles', {}, function(response){
      //Success
      a.matchAjax = false;

      data.matches = response.data;

      //log(data);

      $.each(data.matches, function(index, match){
        match.realname = data.lang.frontpage.match_profiles[match.code];
        match.scoredom = getStarsFromScore(match.score);
      });

      a.render('#content-sidebar-frontpage-template', data, '#content-sidebar');

    }, function(){
      //Failure
      //log('getMatches failed');
      a.matchAjax = false;

    }, 'GET');

    a.matchAjax = jqXHR;
    return jqXHR;

  };

  //Gets possibilities
  a.getPossibilities = function(){
    //log('getPossibilities');

    if(a.possibilityAjax !== false){
      //log('Aborting old query.');
      a.possibilityAjax.abort();
      a.possibilityAjax = false;
    }

    var jqXHR = a.apiCall('jobs/possibilities', {}, function(response){
      //Success
      a.possibilityAjax = false;

      var data = {lang: a.translations[a.currentLanguage]};
      data.jobs = response.data;
      data.keywords = a.user.data.filters.keywords ? a.user.data.filters.keywords.value : '';
      data.limited = response.metadata.limited;
      data.ad_count = response.metadata.ad_count;

      if(data.ad_count.difference > 0) {
        data.ad_count.difference = '+'+data.ad_count.difference+'';
      }

      a.render('#content-page-frontpage-template', data, '#content-page');

      $('#form-possibilities input.text-keywords').tagit({
        availableTags: data.lang.autocomplete.keywords,
        afterTagAdded: function(event, ui) {
          if(ui.duringInitialization) {
            return false;
          }
          //log(' -- Tag added!');
          a.checkForFilterUpdate();
        },
        afterTagRemoved: function(event, ui) {
          if(ui.duringInitialization) {
            return false;
          }
          //log(' -- Tag removed!');
          a.checkForFilterUpdate();
        }
      });

      //Preselect profession
      var profession = '';
      if(a.user.data.filters.profession_code !== undefined) {
        profession = a.user.data.filters.profession_code.value;
        if($('#possibilities-what option[value="'+profession+'"]').length > 0){
          $('#possibilities-what option[value="'+profession+'"]').prop('selected', true);
          $('#possibilities-what').closest('.custom-select').find('label').html($('#possibilities-what option:selected').text());
        }
      }

      //Preselect area
      var area = '';
      if(a.user.data.filters.area !== undefined) {
        area = a.user.data.filters.area.value;
        if($('#possibilities-where option[value="'+area+'"]').length > 0){
          $('#possibilities-where option[value="'+area+'"]').prop('selected', true);
          $('#possibilities-where').closest('.custom-select').find('label').html($('#possibilities-where option:selected').text());
        }
      }

      $.each($('#page-frontpage .custom-select'), function(i, obj){
        $(obj).find('select').off().on('change', function(){
          //log('Select changed!');
          $(this).closest('.custom-select').find('label').html($(this).find('option:selected').text());
          a.doFilterUpdate();
        });
      });

      a.lockTagit = false;

      $('.tagit-new input').focus();

    }, function(){
      //Failure
      //log('getPossibilities failed');
      a.possibilityAjax = false;
      a.lockTagit = false;

    }, 'GET');

    a.possibilityAjax = jqXHR;
    return jqXHR;
  };

  // Summary ----

  //Saves summary
  a.saveSummary = function(){
    //log('saveSummary');
    var data = {lang: a.translations[a.currentLanguage]};

    var container = $(this).closest('.editor');
    $(container).find('.error').removeClass('error');


    var education_level = $(container).find('[name="education_level"]').val();
    if(education_level === ''){
      $(container).find('[name="education_level"]').closest('fieldset').addClass('error');
    }

    //type 1: just keyval
    //type 2: just keywords
    //type 3: 0: no, 1: yes, 2: not set

    var summaryData = {
      summary_fulltime: {
        'key': 'summary_fulltime',
        'type': 1,
        'keywords': '',
        'value': $(container).find('[name="fulltime"]').is(':checked') ? 1 : 0
      },
      summary_parttime: {
        'key': 'summary_parttime',
        'type': 1,
        'keywords': '',
        'value': $(container).find('[name="parttime"]').is(':checked') ? 1 : 0
      },
      summary_shifts: {
        'key': 'summary_shifts',
        'type': 1,
        'keywords': '',
        'value': $(container).find('[name="shifts"]').is(':checked') ? 1 : 0
      },
      summary_evenings_weekends: {
        'key': 'summary_evenings_weekends',
        'type': 1,
        'keywords': '',
        'value': $(container).find('[name="evenings_weekends"]').is(':checked') ? 1 : 0
      },
      location_home: {
        'key': 'location_home',
        'type': 1,
        'keywords': '',
        'value': $(container).find('[name="home"]').is(':checked') ? 1 : 0
      },
      location_abroad: {
        'key': 'location_abroad',
        'type': 1,
        'keywords': '',
        'value': $(container).find('[name="abroad"]').is(':checked') ? 1 : 0
      },
      card_sanssi: {
        'key': 'card_sanssi',
        'type': 1,
        'keywords': '',
        'value': $(container).find('[name="sanssi"]').is(':checked') ? 1 : 0
      },
      card_duuni: {
        'key': 'card_duuni',
        'type': 1,
        'keywords': '',
        'value': $(container).find('[name="duuni"]').is(':checked') ? 1 : 0
      },
      professional_keywords: {
        'key': 'professional_keywords',
        'type': 2,
        'keywords': $(container).find('[name="professional_keywords"]').val(),
        'value': 0
      }
    };

    //log($(container).find('.error').length + ' errors found!');
    if($(container).find('.error').length === 0){

      var xhrs = [];
      var failure = false;

      //Save education level
      xhrs.push(a.apiCall('me', JSON.stringify({
        'data': {
          'education_level': education_level
        }
      }), function(response){
        //Successs
        //log('Education level saved');
      }, function(){
        failure = true;
      }, 'POST'));

      //Save all other data
      xhrs.push(a.apiCall('me/extras', JSON.stringify({
        'data': summaryData
      }), function(response){
        //Successs
        //log('Summary saved');
      }, function(){
        failure = true;
      }, 'POST'));

      $.when.apply(null, xhrs).done(function() {
        if(failure){
          a.message('profile_data_save_fail');
        } else {
          a.message('profile_data_saved');
        }
        a.redrawAll(true);
      });

    } else {
      a.message('profile_data_not_valid');
    }

  };

  // Link ----

  //Adds new link
  a.addNewLink = function(){
    //log('addNewLink');
    var container = $(this).closest('.new-link').find('.container');
    var data = {lang: a.translations[a.currentLanguage]};
    var template = window.Handlebars.compile($('#form-new-link-template').html());
    var dom = $(template(data));
    $(container).append(dom);
    $.each($(dom).find('.keywords[data-autocomplete]'), function(i, el){
      $(el).tagit({
        availableTags: data.lang.autocomplete[$(el).data('autocomplete')]
      });
    });

    $('#content-page input[type="text"]').placeholder();
    $(dom).slideDown();
  };

  //Saves contact info
  a.saveContact = function(){
    //log('saveContact');
    var data = {lang: a.translations[a.currentLanguage]};
    var container = $(this).closest('.editor');
    $(container).find('.error').removeClass('error');

    var contactdata = {};

    var links = [];
    var remove_links = [];

    var id;

    //log('Let\'s twist again!');

    $.each($(container).find('.contact.link'), function(i, pos){
      if($(pos).hasClass('remove')){
        //Remove this one
        id = $(pos).data('linkid');
        //log('Remove link: '+id);
        remove_links.push(id);

      } else {

        //Skip totally empty and untouched new fields
        if(
          $(pos).hasClass('new') &&
          $(pos).find('[name="value"]').val() === '' &&
          $(pos).find('[name="keywords"]').val() === ''
        ){
          //log('Skipping empty');
          return true;
        }

        //Validate required fields
        $.each($(pos).find('.required'), function(i, input){
          if($.trim( $(input).val() ) === ''){
            $(input).closest('fieldset').addClass('error');
          }
        });

        var temp = {
          type: 1,
          key: 'link_'+links.length,
          value: $(pos).find('[name="value"]').val(),
          keywords: $(pos).find('[name="keywords"]').val(),
          category: 'links'
        };

        if(temp.value.indexOf('http://') === -1 && temp.value.indexOf('https://')){
          temp.value = 'http://'+temp.value;
        }

        if($(pos).data('linkid') !== undefined && !$(pos).hasClass('new')){
          temp.id = $(pos).data('linkid');
        }

        links.push(temp);

        //log(temp);

      }

    });

    $.each($(container).find('.contact[data-key="email"]'), function(i, pos){
      if($(pos).find('[name="value"]').val() !== '' && !emailCheck.test($(pos).find('[name="value"]').val())){
        $(pos).find('[name="value"]').closest('fieldset').addClass('error');
      }

      var temp = {
        type: 1,
        key: 'contact_email',
        value: $(pos).find('[name="value"]').val(),
        keywords: $(pos).find('[name="keywords"]').val(),
      };

      if($(pos).data('id') !== ''){ temp.id = $(pos).data('id'); }
      contactdata[temp.key] = temp;

    });

    $.each($(container).find('.contact[data-key="phone"]'), function(i, pos){
      var temp = {
        type: 1,
        key: 'contact_phone',
        value: $(pos).find('[name="value"]').val(),
        keywords: $(pos).find('[name="keywords"]').val(),
      };

      if($(pos).data('id') !== ''){ temp.id = $(pos).data('id'); }
      contactdata[temp.key] = temp;

    });

    //log(contactdata);

    //log($(container).find('.error').length + ' errors found!');

    if($(container).find('.error').length === 0){
      var xhrs = [];
      var failure = false;
      $.each(remove_links, function(i, id){
        var xhr = a.apiCall('me/extras/'+id, {}, function(){
          //Successs
          //log('Link removed');
        }, function(){
          failure = true;
        }, 'DELETE');
        xhrs.push(xhr);
      });

      $.each(links, function(i, link){

        var data = {};
        data[link.key] = link;

        var xhr = a.apiCall('me/extras', JSON.stringify({
          'data': data
        }), function(){
          //Successs
          //log('Link saved');
        }, function(){
          failure = true;
        }, 'POST');
        xhrs.push(xhr);
      });

      var xhr = a.apiCall('me/extras', JSON.stringify({
        'data': contactdata
      }), function(response){
        //Successs
        //log('contactdata saved');
      }, function(){
        failure = true;
      }, 'POST');
      xhrs.push(xhr);

      $.when.apply(null, xhrs).done(function() {
        if(failure){
          a.message('profile_data_save_fail');
        } else {
          a.message('profile_data_saved');
        }
        a.redrawAll(true);
      });

    } else {
      a.message('profile_data_not_valid');
    }
  };

  // Language ----

  //Adds new language
  a.addNewLanguage = function(){
    //log('addNewLanguage');
    var container = $(this).closest('.new-languages').find('.container');
    var data = {lang: a.translations[a.currentLanguage]};
    var template = window.Handlebars.compile($('#form-new-language-template').html());
    var dom = $(template(data));
    $(container).append(dom);
    $.each($(dom).find('.keywords[data-autocomplete]'), function(i, el){
      $(el).tagit({
        availableTags: data.lang.autocomplete[$(el).data('autocomplete')]
      });
    });

    $('#content-page input[type="text"]').placeholder();
    $(dom).slideDown();
  };

  // Skills ----

  //Saves skills
  a.saveSkills = function(){
    //log('saveSkills');
    var data = {lang: a.translations[a.currentLanguage]};
    var container = $(this).closest('.editor');
    $(container).find('.error').removeClass('error');

    var skilldata = {};
    var remove_languages = [];
    var id;

    $.each($(container).find('.editrow.experience'), function(i, pos){
      //Validate required fields
      $.each($(pos).find('.required'), function(i, input){
        if($.trim( $(input).val() ) === ''){
          $(input).closest('fieldset').addClass('error');
        }
      });

      var key = $(pos).data('key');

      skilldata[key] = {
        type: 1,
        key: key,
        value: $(pos).find('[name="years"]').val(),
        keywords: $(pos).find('[name="keywords"]').val(),
        category: 'experience'
      };

    });

    //log(skilldata);

    $.each($(container).find('.editrow.primary-experience'), function(i, pos){
      //Validate required fields
      $.each($(pos).find('.required'), function(i, input){
        if($.trim( $(input).val() ) === ''){
          $(input).closest('fieldset').addClass('error');
        }
      });

      skilldata.primary_experience_type = {
        type: 2,
        key: 'primary_experience_type',
        value: $(pos).find('[name="primary-experience-type"]').val(),
        keywords: $(pos).find('[name="keywords"]').val()
      };

      skilldata.primary_experience_years = {
        type: 3,
        key: 'primary_experience_years',
        value: $(pos).find('[name="primary-experience-years"]').val(),
        keywords: ''
      };

    });

    $.each($(container).find('.subrow.language'), function(i, pos){
      if($(pos).hasClass('remove')){
        //Remove this one
        id = $(pos).data('languageid');
        //log('Remove position: '+id);
        remove_languages.push(id);
      } else {
        //Skip totally empty and untouched new fields
        if(
          $(pos).hasClass('new') &&
          $(pos).find('[name="language"]').val() === '' &&
          $(pos).find('[name="language-skill"]').val() === '' &&
          $(pos).find('[name="keywords"]').val() === ''
        ){
          //log('Skipping empty');
          return true;
        }
        //Validate required fields
        $.each($(pos).find('.required'), function(i, input){
          if($.trim( $(input).val() ) === ''){
            $(input).closest('fieldset').addClass('error');
          }
        });

        skilldata[$(pos).find('[name="language"]').val()] = {
          type: 1,
          key: $(pos).find('[name="language"]').val(),
          value: $(pos).find('[name="language-skill"]').val(),
          keywords: $(pos).find('[name="keywords"]').val(),
          category: 'languages'
        };

        if($(pos).data('languageid') !== undefined && !$(pos).hasClass('new')){
          skilldata[$(pos).find('[name="language"]').val()].id = $(pos).data('languageid');
        }

        //log(skilldata[$(pos).find('[name="language"]').val()]);

      }
    });


    //log($(container).find('.error').length + ' errors found!');

    if($(container).find('.error').length === 0){
      var xhrs = [];
      var failure = false;
      $.each(remove_languages, function(i, id){
        //log('remove language ' + id);
        var xhr = a.apiCall('me/skills/'+id, {}, function(){
          //Successs
          //log('Language removed');
        }, function(){
          failure = true;
        }, 'DELETE');
        xhrs.push(xhr);
      });

      var xhr = a.apiCall('me/skills', JSON.stringify({
        'data': skilldata
      }), function(response){
        //Successs
        //log('Skilldata saved');
      }, function(){
        failure = true;
      }, 'POST');
      xhrs.push(xhr);

      $.when.apply(null, xhrs).done(function() {
        if(failure){
          a.message('profile_data_save_fail');
        } else {
          a.message('profile_data_saved');
        }
        a.redrawAll(true);
      });
    } else {
      a.message('profile_data_not_valid');
    }

  };


  // Education ----

  //Add new education
  a.addNewEducation = function(){
    //log('addNewEducation');
    var container = $(this).closest('.new-education').find('.container');
    var data = {lang: a.translations[a.currentLanguage]};
    var template = window.Handlebars.compile($('#form-new-education-template').html());
    var dom = $(template(data));
    $(container).append(dom);

    $.each($(dom).find('.keywords[data-autocomplete]'), function(i, el){
      $(el).tagit({
        availableTags: data.lang.autocomplete[$(el).data('autocomplete')]
      });
    });

    $.each($(dom).find('input[data-autocomplete="degrees"]'), function(i, el){
      $(el).autocomplete({
        source: data.lang.autocomplete[$(el).data('autocomplete')]
      });
    });

    $('#content-page input[type="text"]').placeholder();
    $(dom).slideDown();
  };

  //Save education data
  a.saveEducation = function(){
    //log('saveEducation');
    var data = {lang: a.translations[a.currentLanguage]};
    var container = $(this).closest('.editor');
    $(container).find('.error').removeClass('error');
    var education = [];
    var remove = [];
    var id;
    //log('Let\'s twist again!');
    $.each($(container).find('.education'), function(i, pos){
      if($(pos).hasClass('remove')){
        //Remove this one
        id = $(pos).data('educationid');
        //log('Remove education: '+id);
        remove.push(id);
      } else {
        //Skip totally empty and untouched new fields
        if(
          $(pos).hasClass('new') &&
          $(pos).find('[name="level"]').val() === '' &&
          $(pos).find('[name="school"]').val() === '' &&
          $(pos).find('[name="degree"]').val() === '' &&
          $(pos).find('[name="end_year"]').val() === '' &&
          $(pos).find('[name="keywords"]').val() === '' &&
          !$(pos).find('[name="not_ended"]').is(':checked')
        ){
          //log('Skipping empty');
          return true;
        }
        //Validate required fields
        $.each($(pos).find('.required'), function(i, input){
          if($.trim( $(input).val() ) === ''){
            $(input).closest('fieldset').addClass('error');
          }
        });
        //Validate number
        $.each($(pos).find('.number'), function(i, input){
          if( $(input).val() !== '' && parseInt($(input).val(), 10) === 0 ){
            $(input).closest('fieldset').addClass('error');
          }
          if( $(input).hasClass('year') && $(input).val() !== '' && (parseInt($(input).val(), 10) < 1000 || parseInt($(input).val(), 10) > 3000) ){
            $(input).closest('fieldset').addClass('error');
          }
        });
        if (!$(pos).find('[name="not_ended"]').is(':checked')){
          if(
              ($.trim( $(pos).find('[name="end_year"]').val() ) === '') ||
              (parseInt($(pos).find('[name="end_year"]').val(), 10) < 1000) ||
              (parseInt($(pos).find('[name="end_year"]').val(), 10) > 3000)
            ){
            $(pos).find('[name="end_year"]').closest('fieldset').addClass('error');
          }
        }
        var temp = {
          level: $(pos).find('[name="level"]').val(),
          school: $(pos).find('[name="school"]').val(),
          degree: $(pos).find('[name="degree"]').val(),
          keywords: $(pos).find('[name="keywords"]').val()
        };
        if ($(pos).find('[name="not_ended"]').is(':checked')){
          //log('NOT ENDED! current = 1');
          temp.current = 1;
          temp.end_year = '';
        } else {
          //log('ENDED! current = 0');
          temp.current = 0;
          temp.end_year = $(pos).find('[name="end_year"]').val();
        }
        if($(pos).data('educationid') !== undefined && !$(pos).hasClass('new')){
          temp.id = $(pos).data('educationid');
        }
        education.push(temp);
        //log(temp);
      }
    });
    //log($(container).find('.error').length + ' errors found!');
    if($(container).find('.error').length === 0){
      var xhrs = [];
      var failure = false;
      $.each(remove, function(i, id){
        var xhr = a.apiCall('me/education/'+id, {}, function(){
          //Successs
          //log('Education removed');
        }, function(){
          failure = true;
        }, 'DELETE');
        xhrs.push(xhr);
      });
      var xhr = a.apiCall('me/education', JSON.stringify({
        'data': education
      }), function(response){
        //Successs
        //log('Education saved');
      }, function(){
        failure = true;
      }, 'POST');
      xhrs.push(xhr);
      $.when.apply(null, xhrs).done(function() {
        if(failure){
          a.message('profile_data_save_fail');
        } else {
          a.message('profile_data_saved');
        }
        a.redrawAll(true);
      });
    } else {
      a.message('profile_data_not_valid');
    }
  };


  // Positions ----

  //Adds new position
  a.addNewPosition = function(){
    //log('addNewPosition');
    var container = $(this).closest('.new-positions').find('.container');
    var data = {lang: a.translations[a.currentLanguage]};
    var template = window.Handlebars.compile($('#form-new-position-template').html());
    var dom = $(template(data));
    $(container).append(dom);
    $.each($(dom).find('.keywords[data-autocomplete]'), function(i, el){
      $(el).tagit({
        availableTags: data.lang.autocomplete[$(el).data('autocomplete')]
      });
    });

    $('#content-page input[type="text"]').placeholder();
    $(dom).slideDown();
  };

  //Save all positions
  a.savePositions = function(){
    //log('savePositions');
    var data = {lang: a.translations[a.currentLanguage]};
    var container = $(this).closest('.editor');
    $(container).find('.error').removeClass('error');
    var positions = [];
    var remove = [];
    var id;
    //log('Let\'s twist again!');
    $.each($(container).find('.position'), function(i, pos){
      if($(pos).hasClass('remove')){
        //Remove this one
        id = $(pos).data('positionid');
        //log('Remove position: '+id);
        remove.push(id);
      } else {
        //Skip totally empty and untouched new fields
        if(
          $(pos).hasClass('new') &&
          $(pos).find('[name="company"]').val() === '' &&
          $(pos).find('[name="title"]').val() === '' &&
          $(pos).find('[name="start_month"]').val() === '' &&
          $(pos).find('[name="start_year"]').val() === '' &&
          $(pos).find('[name="end_month"]').val() === '' &&
          $(pos).find('[name="end_year"]').val() === '' &&
          $(pos).find('[name="keywords"]').val() === '' &&
          !$(pos).find('[name="not_ended"]').is(':checked')
        ){
          //log('Skipping empty');
          return true;
        }
        //Validate required fields
        $.each($(pos).find('.required'), function(i, input){
          if($.trim( $(input).val() ) === ''){
            $(input).closest('fieldset').addClass('error');
          }
        });
        //Validate number
        $.each($(pos).find('.number'), function(i, input){
          if( $(input).val() !== '' && parseInt($(input).val(), 10) === 0 ){
            $(input).closest('fieldset').addClass('error');
          }
          if( $(input).hasClass('month') && $(input).val() !== '' && parseInt($(input).val(), 10) > 12){
            $(input).closest('fieldset').addClass('error');
          }
          if( $(input).hasClass('year') && $(input).val() !== '' && (parseInt($(input).val(), 10) < 1000 || parseInt($(input).val(), 10) > 3000) ){
            $(input).closest('fieldset').addClass('error');
          }
        });
        if (!$(pos).find('[name="not_ended"]').is(':checked')){
          if(
              ($.trim( $(pos).find('[name="end_month"]').val() ) === '') ||
              (parseInt($(pos).find('[name="end_month"]').val(), 10) > 12)
            ){
            $(pos).find('[name="end_month"]').closest('fieldset').addClass('error');
          }
          if(
              ($.trim( $(pos).find('[name="end_year"]').val() ) === '') ||
              (parseInt($(pos).find('[name="end_year"]').val(), 10) < 1000) ||
              (parseInt($(pos).find('[name="end_year"]').val(), 10) > 3000)
            ){
            $(pos).find('[name="end_year"]').closest('fieldset').addClass('error');
          }
        }
        var temp = {
          company: $(pos).find('[name="company"]').val(),
          start_month: $(pos).find('[name="start_month"]').val(),
          start_year: $(pos).find('[name="start_year"]').val(),
          title: $(pos).find('[name="title"]').val(),
          keywords: $(pos).find('[name="keywords"]').val()
        };
        if ($(pos).find('[name="not_ended"]').is(':checked')){
          //log('NOT ENDED! current = 1');
          temp.current = 1;
          temp.end_month = '';
          temp.end_year = '';
        } else {
          //log('ENDED! current = 0');
          temp.current = 0;
          temp.end_month = $(pos).find('[name="end_month"]').val();
          temp.end_year = $(pos).find('[name="end_year"]').val();
        }
        if($(pos).data('positionid') !== undefined && !$(pos).hasClass('new')){
          temp.id = $(pos).data('positionid');
        }
        positions.push(temp);
        //log(temp);
      }
    });
    //log($(container).find('.error').length + ' errors found!');
    if($(container).find('.error').length === 0){
      var xhrs = [];
      var failure = false;
      $.each(remove, function(i, id){
        var xhr = a.apiCall('me/workhistory/'+id, {}, function(){
          //Successs
          //log('Position removed');
        }, function(){
          failure = true;
        }, 'DELETE');
        xhrs.push(xhr);
      });
      var xhr = a.apiCall('me/workhistory', JSON.stringify({
        'data': positions
      }), function(response){
        //Successs
        //log('Workhistory saved');
      }, function(){
        failure = true;
      }, 'POST');
      xhrs.push(xhr);
      $.when.apply(null, xhrs).done(function() {
        if(failure){
          a.message('profile_data_save_fail');
        } else {
          a.message('profile_data_saved');
        }
        a.redrawAll(true);
      });
    } else {
      a.message('profile_data_not_valid');
    }
  };

  //Update filters to the backend
  a.updateFilters = function(position, area, keywords){
    //log('updateFilters');

    if(a.filterAjax !== false){
      //log('Aborting old query.');
      a.filterAjax.abort();
      a.filterAjax = false;
    }

    var jqXHR = a.apiCall('me/filters', JSON.stringify({
      'data': {
        'keywords': keywords,
        'area': area,
        'profession_code': position
      }
    }), function(response){
      //Success
      a.filterAjax = false;

      //log(response);

      if(a.user.data.filters === undefined ||
        a.user.data.filters.profession_code === undefined ||
        a.user.data.filters.profession_code.value === undefined ||
        a.user.data.filters.area === undefined ||
        a.user.data.filters.area.value === undefined ||
        a.user.data.filters.keywords === undefined ||
        a.user.data.filters.keywords.value === undefined ||
        (a.user.data.filters.profession_code.value !== response.data.profession_code.value) ||
        (a.user.data.filters.area.value !== response.data.area.value) ||
        (a.user.data.filters.keywords.value !== response.data.keywords.value) ){

        a.user.data.filters = response.data;

        a.getPossibilities();
        a.getMatches();

      }

    }, function(){
      //Failure
      //log('updateFilters failed');
      a.filterAjax = false;

    }, 'POST');

    a.filterAjax = jqXHR;
    return jqXHR;

  };

  //Update filters
  a.doFilterUpdate = function(){
    //log('doFilterUpdate');
    var form = $('#form-possibilities');

    var profession = $(form).find('select[name="what"]').val();
    var area = $(form).find('select[name="where"]').val();
    var keywords = $(form).find('input[name="keywords"]').val();

    a.updateFilters(profession, area, keywords);
  };

  //Check if filters need update
  a.checkForFilterUpdate = function(){
    if(!a.lockTagit){
      a.lockTagit = true;
      //log('checkForFilterUpdate');
      var form = $('#form-possibilities');

      var profession = $(form).find('select[name="what"]').val();
      var area = $(form).find('select[name="where"]').val();
      var keywords = $(form).find('input[name="keywords"]').val();

      ////log('Old keywords: '+a.user.data.filters.keywords.value);
      ////log('Keywords now: '+keywords);

      if(keywords !== a.user.data.filters.keywords.value) {
        //log('Keywords changed, updateFilters!');
        a.updateFilters(profession, area, keywords);
      } else {
        a.lockTagit = false;
      }
    }


  };

  //Saves profile image
  a.saveProfileImage = function(image){

    //log('a.saveProfileImage');
    //log(image);

    var formData = new FormData();
    var blob = dataURItoBlob(image);
    formData.append('image', blob);

    var jqXHR = a.apiCall('me', formData, function(response){
      a.user.data = response.data;
      a.user.metadata = response.metadata;
      a.updateTestProgress();
      $('#uploader').fadeOut(200);
      a.redrawAll(true);

    }, function(){
      //Failure
      //log('Profile image cannot be saved!');
      a.message('uploader_save_error');
      $('#uploader').fadeOut(200);

    }, 'POST', true); //true for raw data

    return jqXHR;

  };

  //Opens job in new "window"
  a.openJob = function(id){
    a.loading.start();
    //log('openJob');

    var apiRequest = 'job/'+id;

    if (a.matchOpen !== false && $('.match.active').eq(0).length === 1){
      var profile = $('.match.active').eq(0).data('matchid');
      apiRequest += '/profile/'+profile;
    }

    window.location.hash = 'job-'+id;

    //log(apiRequest);

    var jqXHR = a.apiCall(apiRequest, {}, function(response){
      //Success
      $('#button-back').attr('class', 'close-job').fadeIn(200);

      var data = {lang: a.translations[a.currentLanguage]};
      data.job = response.data;

      //log(data);

      $.each(a.user.data.tests, function(index, test){
        if(test.instrument_id === 102 && test.score !== null && test.score_key !== null) {
          //log('This guy is tested!');
          data.tested = true;
          log(data.job.profile);
          if(data.job.profile !== undefined && data.job.profile.score !== undefined){
            data.job.scoredom = getStarsFromScore(data.job.profile.score);
          }
        }
      });

      if(data.job.profile !== undefined){
        var competences = data.job.profile.competences;
        competences = shuffle(competences);
        competences = competences.slice(0,2);

        var competence_texts = [];
        $.each(competences, function(i, competence){
          competence_texts.push( data.lang.job.competences[competence] );
        });

        var behavioral_models = data.job.profile.behavioral_models;
        behavioral_models = shuffle(behavioral_models);
        behavioral_models = behavioral_models.slice(0,2);

        var behavioral_model_texts = [];
        $.each(behavioral_models, function(i, behavioral_model){
          behavioral_model_texts.push( data.lang.job.behavioral_models[behavioral_model] );
        });

        data.job.profile = {
          competences: competence_texts,
          behavioral_models: behavioral_model_texts
        };

      }

      data.job.parsed = a.getParsedData(data.job);

      data.job.url = 'http://www.mol.fi/tyopaikat/tyopaikkatiedotus/haku/'+data.job.json_ad.ilmoitusnumero+'_fi.htm';

      data.fromMatch = (a.matchOpen !== false);

      $.when(
        $('#content-sidebar').fadeOut(200),
        $('#page-frontpage').fadeOut(200),
        $('#page-match').fadeOut(200)
      ).then(function(){
        a.render('#content-sidebar-job-template', data, '#sidebar-job');
        a.render('#content-page-job-template', data, '#page-job');
        a.scrollUp();
        $('#sidebar-frontpage').hide();
        $('#sidebar-job').show();
        $('#content-sidebar').fadeIn(200);
        $('.sidebar-job .anim-in').css({opacity: 0});
        $('#page-job').fadeIn(200, function(){
          a.loading.stop();
          $.each($('.sidebar-job .anim-in'), function(i, el){
            var that = el;
            setTimeout(function(){
              $(that).animate({opacity: 1}, 300);
            }, 150*i);
          });
        });
      });

    }, function(){
      //Failure
      //log('openJob failed');
      a.loading.stop();
    }, 'GET');
    return jqXHR;

  };

  //Refresh match
  a.refreshMatch = function(id, area){
    a.loading.start();
    a.matchArea = area;

    a.openMatch(id, true);
  };

  //Open match
  a.openMatch = function(id, noFade){
    //log('openMatch');
    a.loading.start();
    a.matchOpen = true;

    if (a.matchArea === false){
      a.matchArea = a.user.data.filters.area.value;
    }

    $('.pageselector[data-page="frontpage"]').removeClass('active');

    $('.match').removeClass('active');
    var match = $('.match[data-matchid="'+id+'"]');
    $(match).addClass('active');

    var score = $(match).data('score');

    function getData(){
      var jqXHR = a.apiCall('jobs/matches/'+id, {area: a.matchArea, keywords: a.matchKeywords}, function(response){
        //Success
        $('#button-back').attr('class', 'close-match')/*.fadeIn(200)*/;
        var data = {lang: a.translations[a.currentLanguage]};

        data.jobs = response.data;

        data.match = {
          score: score,
          scoredom: getStarsFromScore(score),
          name: data.lang.frontpage.match_profiles[response.metadata.code].toLowerCase()
        };

        a.matchOpen = data.match;

        //log(data);

        $.when(
          $('#page-frontpage').fadeOut(200)
        ).then(function(){
          a.loading.trickle();

          a.render('#content-page-match-template', data, '#page-match');

          if($('#match-possibilities-where option[value="'+a.matchArea+'"]').length > 0){
            $('#match-possibilities-where option[value="'+a.matchArea+'"]').prop('selected', true);
            $('#match-possibilities-where').closest('.custom-select').find('label').html($('#match-possibilities-where option:selected').text());
          }

          $.each($('#page-match .custom-select'), function(i, obj){
            $(obj).find('select').off().on('change', function(){
              //log('Select changed!');
              $(this).closest('.custom-select').find('label').html($(this).find('option:selected').text());
              a.refreshMatch(id, $(this).val());
            });
          });

          $('#form-match-possibilities input.text-keywords').val(a.matchKeywords);

          $('#form-match-possibilities input.text-keywords').tagit({
            availableTags: data.lang.autocomplete.keywords,
            afterTagAdded: function(event, ui) {
              if(ui.duringInitialization) {
                return false;
              }
              //log(' -- Tag added!');
              a.matchKeywords = $('#form-match-possibilities input.text-keywords').val();
              a.refreshMatch(id, a.matchArea);
            },
            afterTagRemoved: function(event, ui) {
              if(ui.duringInitialization) {
                return false;
              }
              //log(' -- Tag removed!');
              a.matchKeywords = $('#form-match-possibilities input.text-keywords').val();
              a.refreshMatch(id, a.matchArea);
            }
          });

          if(noFade === undefined || noFade === false ) {
            a.scrollUp();
          }

          $('#page-match').fadeIn(200, function(){
            a.loading.stop();
          });
        });

      }, function(){
        //Failure
        //log('openMatch failed');
        a.loading.stop();
      }, 'GET');

    }

    if(noFade !== undefined && noFade === true){
      getData();
    } else {
      $.when(
        $('#page-match').fadeOut(200)
      ).then(function(){
        a.loading.trickle();
        getData();
      });
    }

  };

  //Closes match
  a.closeMatch = function(){
    //log('closeMatch');

    $('.match').removeClass('active');
    a.matchOpen = false;
    a.matchArea = false;
    a.matchKeywords = '';

    $.when(
      $('#page-match').fadeOut(200),
      $('#button-back').attr('class', '')/*.fadeOut(200)*/
    ).then(function(){
      $('#page-frontpage').fadeIn(200);
    });

  };

  //Submits discount code to backend
  a.submitDiscount = function(testid, code){
    //log('submitDiscount');
    a.loading.start();

    var jqXHR = a.apiCall('test/discount', {test_id: testid, code: code}, function(response){
      //Success
      $.when(a.getTests()).then(function(){
        a.message('discount_code_success');
        a.loading.stop();
      });

    }, function(response){
      //Failure
      a.message('discount_code_failed');
      a.loading.stop();
    }, 'POST');

  };

  //Closes job
  a.closeJob = function(){
    //log('closeJob');
    var data = {lang: a.translations[a.currentLanguage]};
    window.location.hash = 'frontpage';

    if(a.matchOpen !== false){
      //log('Match is open.');
      $.when(
        $('#content-sidebar').fadeOut(200),
        $('#page-job').fadeOut(200),
        $('#button-back').attr('class', '').fadeOut(200)
      ).then(function(){
        $('#sidebar-job').hide().html('');
        $('#page-job').html('');

        $('#sidebar-frontpage').show();
        $('#content-sidebar').fadeIn(200);
        $('#page-match').fadeIn(200);
      });

    } else {
      //log('Match is not open!');
      $.when(
        $('.pageselector[data-page="frontpage"]').addClass('active'),
        $('#content-sidebar').fadeOut(200),
        $('#page-job').fadeOut(200),
        $('#button-back').attr('class', '').fadeOut(200)
      ).then(function(){
        $('#sidebar-job').hide().html('');
        $('#page-job').html('');

        $('#sidebar-frontpage').show();
        $('#content-sidebar').fadeIn(200);
        $('#page-frontpage').fadeIn(200);
      });

    }

  };

  //Saves open tests before navigating
  a.saveOpenTests = function(){
    var openTests = [];

    $.each($('#page-tests .test.open'), function(i, el){
      var test = $(el).data('test');
      openTests.push(test);
    });

    settings.openTests = openTests;
    saveData(config.datastore, settings);
    //log(settings);
  };

  //Login
  a.login = function(email, password, persistent){
    //log('login');

    var data = {'email': email, 'password': password};

    if(persistent) {
      data.remember_me = true;
    }

    var jqXHR = a.apiCall('login', data, function(response){
      //Success

      settings.apiToken = response.metadata.mazhr_token;
      saveData(config.datastore, settings);

      a.user.loggedIn = true;
      a.user.data = response.data;
      a.user.metadata = response.metadata;
      //log('Logged in!');

      a.showView('#frontpage');

      //Show app
      $('#start').fadeOut(400, function(){

        //Clear form
        var form = $('#form-login');
        $(form).find('input[name="email"]').val('');
        $(form).find('input[name="password"]').val('');
        $(form).find('input[name="remember-me"]').prop('checked', false);

        $('#app').fadeIn();

      });

    }, function(){
      //Failure
      //log('Login failed');
      a.message('login_failed');

    }, 'POST');

    return jqXHR;

  };

  //Logout, destroys session & saved token
  a.logout = function(){
    //log('logout');
    a.loading.start();

    delete settings.apiToken;
    delete settings.tempToken;
    delete settings.openTests;
    saveData(config.datastore, settings);

    //log(settings);

    a.user.loggedIn = false;
    a.user.data = {};
    a.user.metadata = {};
    a.linkedinData = {};

    a.matchOpen = false;
    a.matchArea = false;
    a.matchKeywords = '';

    a.lockTagit = false;
    a.showLoader = false;

    a.filterAjax = false;
    a.possibilityAjax = false;
    a.matchAjax = false;

    a.showView('#start');

    //Show start
    $('#app').fadeOut(400, function(){
      $('#start').fadeIn(400, function(){
        a.loading.stop();

        $('#content-page').html('');

        //Fetch new token
        var jqXHR = a.apiCall('me', {}, function(response){
          //Success
          if(response.metadata.tmpToken !== undefined){
            //log('tempToken was set');
            settings.tempToken = response.metadata.tmpToken;
            saveData(config.datastore, settings);
            //log(settings);
          } else {
            location.reload();
          }
        }, function(response){
          //Failure
          //log(response);
          //log('Logincheck failed, save tempToken');
          var responseData = JSON.parse(response.responseText);
          if(responseData.tmpToken !== undefined){
            //log('tempToken was set');
            settings.tempToken = responseData.tmpToken;
            saveData(config.datastore, settings);
            //log(settings);
          } else {
            location.reload();
          }
        }, 'GET');

      });
    });

  };

  //Updates menu
  a.updateMenu = function(viewName){
    var data = {lang: a.translations[a.currentLanguage]};
    a.render('#menu-template', data, '#menu');
    var menuitem = $('#menu [data-page="'+viewName+'"]');
    if (menuitem.length > 0) {
      $(menuitem).addClass('active');
    }
    a.updateTestProgress();
  };

  //Shows view
  a.showView = function(viewName, force, skipLeaveCheck){

    //Allow only certain views
    if(a.user.loggedIn === false) {
      if(config.publicViews.indexOf(viewName) === -1){
        viewName = '#start';
      }
    } else {
      if(config.allowedViews.indexOf(viewName) === -1){
        viewName = '#frontpage';
      }
    }

    //Check if editing...
    a.onLeaveCheck(skipLeaveCheck, function(){

      if(force === true || a.view !== viewName) {

        var data = {lang: a.translations[a.currentLanguage]};

        //log('Showing view '+viewName);
        a.view = viewName;
        window.location.hash = a.view;

        //This switch handles different views
        switch(viewName) {
        //public
        case '#start': //------------------------
          data.backendUrl = config.backendUrl;

          $.when($('#start').fadeOut(400)).then(function(){
            a.render('#content-page-start-template', data, '#start');
            $('#start').fadeIn(400);
          });

          break;

        case '#login': //------------------------
          data.backendUrl = config.backendUrl;

          $.when($('#start').fadeOut(400)).then(function(){
            a.render('#content-page-login-template', data, '#start');
            $('#page-login input[type="text"]').placeholder();
            $('#start').fadeIn(400);
          });

          break;

        case '#signup': //------------------------
          data.backendUrl = config.backendUrl;
          data.appUrl = config.appUrl;

          $.when(
            $('#start').fadeOut(400)
          ).then(function(){

            if(a.linkedinData !== false){

              if(a.linkedinData.emailAddress !== undefined && a.linkedinData.emailAddress !== ''){

                data.notFromLinkedIn = false;

                data.linkedin = {};
                data.linkedin.name_first = a.linkedinData.firstName;
                data.linkedin.name_last = a.linkedinData.lastName;
                data.linkedin.email = a.linkedinData.emailAddress;

                $('.fullscreenform').fadeIn(400);

              } else {
                data.notFromLinkedIn = true;
              }

            } else {

              data.notFromLinkedIn = true;

            }

            a.render('#content-page-register-template', data, '#start');
            $('#page-register input[type="text"]').placeholder();

            $.each($('.fullscreenform fieldset'), function(i, field){
              var form = $(field).closest('.fullscreenform');
              var formcontroller = $('<div class="formcontroller" data-field="'+i+'"></div>');
              $(field).attr('data-field', i);
              form.find('.formcontrollers').append(formcontroller);
            });

            $.each($('.fullscreenform'), function(i, form){
              $(form).find('fieldset').eq(0).addClass('active');
              $(form).find('.formcontroller').eq(0).addClass('enabled active');
              $(form).find('fieldset.active').find('input').focus();
            });

            $('.fullscreenform .next').on('click', function(){
              var form = $(this).closest('.fullscreenform');
              fullscreenFormNext(form);
            });

            $.each($('#page-register .custom-select'), function(i, obj){
              $(obj).find('select').off().on('change', function(){
                //log('Select changed!');
                $(this).closest('.custom-select').addClass('used');
                $(this).closest('.custom-select').find('label').html($(this).find('option:selected').text());
              });
            });

            if(!data.notFromLinkedIn){
              $('.fullscreenform').fadeIn(400);

              $('.fullscreenform fieldset.active').removeClass('active');
              $('.fullscreenform .formcontroller.active').removeClass('active');

              $.each($('.fullscreenform fieldset'), function(i, field){

                var number = $(field).data('field');

                if( $(field).hasClass('education_levels') ){
                  $(field).addClass('active');
                  $('.fullscreenform').find('.formcontroller[data-field="'+number+'"]').addClass('enabled active');
                  return false;

                } else {

                  $('.fullscreenform').find('.formcontroller[data-field="'+number+'"]').addClass('enabled');

                }

              });
            }

            $('#page-register fieldset').keypress(function(e) {
              if(e.which === 13) {
                var form = $(this).closest('.fullscreenform');
                fullscreenFormNext(form);
              }
            });

            $('#page-register .formcontroller').on('click', function(){
              if($(this).hasClass('enabled') && !$(this).hasClass('active')){
                var number = $(this).data('field');
                var form = $(this).closest('.fullscreenform');
                fullscreenFormGoTo(form, number);
              }
            });

            $('#form-register-submit').on('click', function(){

              var data = {
                first: $('#register_name_first').val(),
                last: $('#register_name_last').val(),
                email: $('#register_email').val(),
                pass1: $('#register_password').val() ? $('#register_password').val() : '',
                pass2: $('#register_password').val() ? $('#register_password').val() : '',
                education_level: $('#register_education_level').val()
              };

              a.apiCall('register', data, function(response){
                //Success
                //log(response);

                if(response.metadata.mazhr_token !== undefined){
                  //log('Got token');
                  settings.apiToken = response.metadata.mazhr_token;
                  saveData(config.datastore, settings);
                }

                a.user.loggedIn = true;
                a.user.data = response.data;
                a.user.metadata = response.metadata;

                a.refreshTempToken(function(){

                  //Update filters too.
                  $.when(a.apiCall('me/filters', JSON.stringify({
                    'data' : {
                      'keywords': '',
                      'area': '',
                      'profession_code': ''
                    }
                  }),
                  function(response){
                    //Success
                    a.user.data.filters = response.data;
                  },
                  function(){
                    //Failure
                    //log('updateFilters failed');
                  }, 'POST')
                  ).then(function(){
                    a.showView('#frontpage');
                    //Show app
                    $('#start').fadeOut(400, function(){
                      $('#app').fadeIn(400, function(){
                        a.showLoader = true;
                      });
                    });
                  });

                }, function(){
                  a.logout();
                });



              }, function(response){
                //Failure
                //log('Register failed');

                var errorjson = JSON.parse(response.responseText);
                if(errorjson.error !== undefined && errorjson.error === 'email_in_use'){
                  a.message('email_in_use');
                } else {
                  a.message('generic_error');
                }


              }, 'POST');

            });

            $('#start').fadeIn(400);

          });

          break;

        /*case '#tryout': //------------------------
          data.backendUrl = config.backendUrl;
          a.render('#content-page-tryout-template', data, '#start');
          break;*/

        //private
        case '#frontpage': //------------------------
          a.loading.start();
          $.when(
            a.updateMenu(viewName.slice(1)),
            $('#content-page').fadeOut(200),
            $('#content-sidebar').fadeOut(200)
          ).then(function(){
            a.loading.trickle();
            $.when(
              a.getPossibilities(),
              a.getMatches()
            ).then(function(){
              a.loading.trickle();
              $('#content-page').fadeIn(200);
              $('.sidebar-frontpage .anim-in').css({opacity: 0});
              $('#content-sidebar').fadeIn(200, function(){
                a.loading.stop();
                $.each($('.sidebar-frontpage .anim-in'), function(i, el){
                  var that = el;
                  setTimeout(function(){
                    $(that).animate({opacity: 1}, 300);
                  }, 150*i);
                });

              });
            });

          });
          break;

        case '#profile': //------------------------
          a.loading.start();
          $.when(
            a.apiCall('me', {}, function(response){
              a.user.data = response.data;
              a.user.metadata = response.metadata;
              a.updateTestProgress();
            }, function(){ a.message('generic_error'); }, 'GET'),
            a.updateMenu(viewName.slice(1)),
            $('#content-page').fadeOut(200),
            $('#content-sidebar').fadeOut(200)
          ).then(function(){
            a.loading.trickle();

            var userdata = a.user.data;

            var image = '';
            if(userdata.image !== null){
              image = config.backendUrl+'/uploads/'+userdata.image;
            }

            //Contact info
            var contact_info = {
              email: {field: 'email', value: '', field_name: data.lang.profile.form.contact_fields.email, keywords_array: [], keywords: ''},
              phone: {field: 'phone', value: '', field_name: data.lang.profile.form.contact_fields.phone, keywords_array: [], keywords: ''}
            };

            function getField(link){
              if(link.indexOf('facebook.com') !== -1){ return 'facebook'; }
              if(link.indexOf('twitter.com') !== -1){ return 'twitter'; }
              if(link.indexOf('linkedin.com') !== -1){ return 'linkedin'; }
              if(link.indexOf('behance.net') !== -1){ return 'behance'; }
              if(link.indexOf('google.com') !== -1){ return 'google-plus'; }
              return 'link';
            }

            var contact_links = [];

            if(userdata.extras.links !== undefined){
              contact_links = userdata.extras.links;
            }
            $.each(contact_links, function(i, link){
              link.field = getField(link.value);
              link.keywords_array = (link.keywords !== undefined && link.keywords !== '') ? link.keywords.split(',') : [];
            });

            //log(contact_links);

            if(userdata.extras.contact_email !== undefined){
              contact_info.email = {
                id: userdata.extras.contact_email.id,
                shown: false,
                field: 'email',
                keywords: userdata.extras.contact_email.keywords,
                keywords_array: (userdata.extras.contact_email.keywords !== undefined && userdata.extras.contact_email.keywords !== '') ? userdata.extras.contact_email.keywords.split(',') : [],
                value: userdata.extras.contact_email.value,
                field_name: data.lang.profile.form.contact_fields.email
              };

              if (contact_info.email.value !== ''){
                contact_info.email.shown = true;
              }

            }

            if(userdata.extras.contact_phone !== undefined){
              contact_info.phone = {
                id: userdata.extras.contact_phone.id,
                shown: false,
                field: 'phone',
                keywords: userdata.extras.contact_phone.keywords,
                keywords_array: (userdata.extras.contact_phone.keywords !== undefined && userdata.extras.contact_phone.keywords !== '') ? userdata.extras.contact_phone.keywords.split(',') : [],
                value: userdata.extras.contact_phone.value,
                field_name: data.lang.profile.form.contact_fields.phone
              };

              if (contact_info.phone.value !== ''){
                contact_info.phone.shown = true;
              }
            }

            //Summary
            var summary = {
              interests: [],
              locations: [],
              cards: [],
              keywords: (userdata.extras !== undefined && userdata.extras.professional_keywords !== undefined && userdata.extras.professional_keywords.keywords !== '') ? userdata.extras.professional_keywords.keywords : '',
              keywords_array: (userdata.extras !== undefined && userdata.extras.professional_keywords !== undefined && userdata.extras.professional_keywords.keywords !== '') ? userdata.extras.professional_keywords.keywords.split(',') : [],
              also_abroad: []
            };

            summary.education_level = {id: userdata.education_level, name: data.lang.education_levels[userdata.education_level]};

            function getFields(fields, lang){
              var found = [];
              for(var i=0; i<fields.length; i++){
                var field = fields[i];
                if(userdata.extras !== undefined && userdata.extras[field.id] !== undefined && userdata.extras[field.id].value === '1'){
                  found.push({id: field.name, name: lang[field.name]});
                }
              }
              return found;
            }

            summary.locations = getFields([
              {'id': 'location_home', 'name': 'home'},
              {'id': 'location_abroad', 'name': 'abroad'},
            ], data.lang.profile.form.locations);

            summary.interests = getFields([
              {'id': 'summary_fulltime', 'name': 'fulltime'},
              {'id': 'summary_parttime', 'name': 'parttime'},
              {'id': 'summary_shifts', 'name': 'shifts'},
              {'id': 'summary_evenings_weekends', 'name': 'evenings_weekends'}
            ], data.lang.profile.form.interests);

            summary.cards = getFields([
              {'id': 'card_sanssi', 'name': 'sanssi'},
              {'id': 'card_duuni', 'name': 'duuni'}
            ], data.lang.profile.form.cards);

            var experience = {};

            function findData(objs, id){
              if(objs !== undefined) {
                for(var i=0; i<objs.length; i++){
                  var obj = objs[i];
                  if(obj.key !== undefined && obj.key === id){
                    obj.show = true;
                    if(obj.value === '' && obj.keywords === '') {
                      obj.show = false;
                    }
                    return obj;
                  }
                }
              }

              return {show: false, key: '', value: ''};
            }

            experience.leadership = findData(userdata.skills.experience, 'leadership');
            experience.superior = findData(userdata.skills.experience, 'superior');
            experience.entrepreneur = findData(userdata.skills.experience, 'entrepreneur');
            experience.abroad = findData(userdata.skills.experience, 'abroad');

            var experience_shown = false;
            $.each(experience, function(i, exp){
              if(exp.show === true){
                experience_shown = true;
                return false;
              }
            });

            //log(experience);
            //log('experience_shown: '+experience_shown);

            var primary_experience = {shown: false, key: '', value: '', years_value: '', years: ''};

            if(userdata.skills.primary_experience_type !== undefined && userdata.skills.primary_experience_years !== undefined){
              primary_experience = userdata.skills.primary_experience_type;
              primary_experience.name = data.lang.profile.primary_experience_types[primary_experience.value];
              primary_experience.keywords_array = (primary_experience.keywords !== undefined && primary_experience.keywords !== '') ? primary_experience.keywords.split(',') : [];

              primary_experience.years_value = userdata.skills.primary_experience_years.value;
              primary_experience.years = parseInt(primary_experience.years_value, 10) === 1 ? primary_experience.years_value+' '+data.lang.date_year_single : primary_experience.years_value+' '+data.lang.date_years;
              primary_experience.shown = false;

              if(primary_experience.value !== '' && primary_experience.years_value !== ''){
                primary_experience.shown = true;
              }
            }

            //Additional data
            data.user = {
              name: userdata.first+' '+userdata.last,
              image: image,
              contact: contact_info,
              contact_links: contact_links,
              positions: (userdata.workhistory !== undefined && userdata.workhistory.length > 0) ? userdata.workhistory : [],
              education: (userdata.education !== undefined && userdata.education.length > 0) ? userdata.education : [],
              summary: summary,
              languages: (userdata.skills.languages !== undefined && userdata.skills.languages.length > 0) ? userdata.skills.languages : [],
              experience: experience,
              experience_shown: experience_shown,
              primary_experience: primary_experience
            };

            $.each(data.user.languages, function(i, language){
              //log(language);
              if(data.lang.profile.languages[language.key] !== undefined){
                language.name = data.lang.profile.languages[language.key].name.capitalizeFirstLetter();
              } else {
                language.name = language.key;
              }

              language.score = getStars(language.value);
              language.keywords_array = (language.keywords !== undefined && language.keywords !== '') ? language.keywords.split(',') : [];
            });

            $.each(data.user.experience, function(i, experience){
              experience.name = data.lang.profile.form.experience_fields[experience.key];
              experience.keywords_array = (experience.keywords !== undefined && experience.keywords !== '') ? experience.keywords.split(',') : [];

              experience.years = parseInt(experience.value, 10) === 1 ? experience.value+' '+data.lang.date_year_single : experience.value+' '+data.lang.date_years;

            });

            //log(data.user);

            $.each(data.user.positions, a.parsePosition);
            $.each(data.user.education, a.parseEducation);

            a.render('#content-sidebar-profile-template', data, '#content-sidebar');
            a.render('#content-page-profile-template', data, '#content-page');
            $('#content-page input[type="text"]').placeholder();

            $.when(
              a.apiCall('tests', {}, function(response){
                //Success
                if(a.user.data.tests.length) {

                  var sorted = [];
                  checkAndAdd(102, a.user.data.tests, sorted);
                  checkAndAdd(201, a.user.data.tests, sorted);
                  checkAndAdd(302, a.user.data.tests, sorted);
                  checkAndAdd(301, a.user.data.tests, sorted);
                  checkAndAdd(357, a.user.data.tests, sorted);
                  checkAndAdd(315, a.user.data.tests, sorted);

                  $.each(sorted, function(key, value){
                    if(response.data[value.instrument_id] !== undefined && value.score_key !== '' && value.score_key !== null){
                      var test_name = response.data[value.instrument_id].name;
                      var score = value.score + '';
                      var diagramdata = {};
                      if(score.length === 1){
                        diagramdata = {
                          value: score,
                          text: {
                            title: data.lang.tests.testdata[test_name].name,
                            description: data.lang.tests.result_texts[score]
                          }
                        };
                        a.render('#diagram-template', diagramdata, '#profile-diagrams', true);
                      } else {
                        diagramdata = {
                          text: {
                            title: data.lang.tests.testdata[test_name].name,
                            description: score
                          }
                        };
                        a.render('#text-result-template', diagramdata, '#text-results', true);
                      }
                    }
                  });
                }
              }, function(){
                //Failure
                //log('Getting profile testdata failed!');
              }, 'GET')
            ).then(function(){
              $('#content-page').fadeIn(200);
              $('#content-sidebar').fadeIn(200, function(){
                a.loading.stop();
              });
            });

          });
          break;

        case '#tests': //------------------------
          a.loading.start();
          $.when(
            a.updateMenu(viewName.slice(1)),
            $('#content-page').fadeOut(200),
            $('#content-sidebar').fadeOut(200)
          ).then(function(){
            a.loading.trickle();

            a.render('#content-sidebar-tests-template', data, '#content-sidebar');

            $.when(
              a.getTests()
            ).then(function(){
              a.loading.trickle();
              $('#content-page').fadeIn(200);
              $('#content-sidebar').fadeIn(200, function(){
                a.loading.stop();
              });
            });

          });
          break;

        case '#settings': //------------------------
          a.loading.start();
          $.when(
            a.updateMenu(viewName.slice(1)),
            $('#content-page').fadeOut(200),
            $('#content-sidebar').fadeOut(200)
          ).then(function(){
            a.loading.trickle();

            data.privacysettings = {
              public_profile_bool: false,
              show: {},
              profile_url: config.profileUrl + '#' + a.user.data.privacy.profile_token
            };

            if(a.user.data.privacy !== undefined && a.user.data.privacy.public_profile === 1) {
              data.privacysettings.public_profile_bool = true;
            }

            if(a.user.data.privacy !== undefined && a.user.data.privacy.show !== null && a.user.data.privacy.show !== ''){
              var arr = a.user.data.privacy.show.split(',');
              $.each(arr, function(i, val){
                data.privacysettings.show[val] = true;
              });
            }

            if(data.privacysettings.public_profile_bool === false && $.isEmptyObject(data.privacysettings.show) ){
              data.privacysettings.show = {
                'workhistory': true,
                'education': true,
                'skills': true,
                'extras': true,
                'tests': true,
                'image': true
              };
            }

            a.render('#content-sidebar-settings-template', data, '#content-sidebar');
            a.render('#content-page-settings-template', data, '#content-page');

            if(data.privacysettings.public_profile_bool === true){
              $('#privacy-checkboxes').show();
              $('#privacy-link').show();
            } else {
              $('#privacy-checkboxes').hide();
              $('#privacy-link').hide();
            }

            $('#content-page input[type="text"]').placeholder();
            $('#content-page').fadeIn(200);
            $('#content-sidebar').fadeIn(200, function(){
              a.loading.stop();
            });
          });
          break;

        }

      }

    }, function(){
      window.location.hash = a.view;
      return false;
    });

  };

  //Initialises the whole thing
  a.init = function(){
    //log('App.init()');

    var ieVersion = getInternetExplorerVersion();

    if(ieVersion !== -1 && ieVersion < 10){
      log('Old IE');
      log('Disabling image cropper');
      a.imageCropperEnabled = false;
      $('html').addClass('old-ie');
    }

    if(ieVersion !== -1){
      log('Adding IE class to html');
      $('html').addClass('ie');
    }

    a.linkedinData = false;

    //Load translations and ping online status
    $.when(
      a.loadTranslation('fi_fi', 'languages/finnish.json'),
      a.loadTranslation('en_en', 'languages/english.json'),
      a.loadTranslation('sv_se', 'languages/swedish.json')
    ).then(function(){

      //Old browser nag
      if(typeof legacyBrowser !== 'undefined') {
        var data = {lang: a.translations[a.currentLanguage]};
        var template = window.Handlebars.compile($('#content-browser-nag-template').html());
        $('#browser-nag').html(template(data));
        $('body').css({'overflow': 'hidden'});
        $('#loader').fadeOut(400, function(){
          $('#browser-nag').fadeIn(400, function(){
          });
        });
        return false;
      }

      if(a.imageCropperEnabled === true){

        //Prepare image cropper
        a.render('#content-uploader-template', {lang: a.translations[a.currentLanguage]}, '#uploader', true);

        $('#image-cropper').cropit({
          imageBackground: false,
          width: 260,
          height: 260,
          exportZoom: 2,
          onFileReaderError: function(){ a.message('uploader_image_error'); $('#uploader').fadeOut(200); },
          onImageLoaded: function(){
            $('#uploader').fadeIn(200);
          },
          onImageError: function(){ a.message('uploader_image_error'); $('#uploader').fadeOut(200); }
        });

        $(document).on('click', '[role="cancel-profile-image"]', function(){
          $('#uploader').fadeOut(200);
        });

        $(document).on('click', '[role="save-profile-image"]', function(){
          var image = $('#image-cropper').cropit('export', {
            type: 'image/jpeg',
            quality: 0.8
          });
          a.saveProfileImage(image);
        });

        $(document).on('click', '#sidebar-profile .imagewrapper', function(){
          $('.cropit-image-input').click();
        });

      } else {

        //Prepare input

        $(document).on('click', '#sidebar-profile .imagewrapper', function(){
          a.message('uploader_oldbrowser');
        });

      }

      //Frontpage with matches
      $(document).on('click', '.pageselector[data-page="frontpage"]', function(){
        if(a.matchOpen !== false){
          a.matchOpen = false;
          a.matchArea = false;
          a.matchKeywords = '';
          a.showView('#frontpage', true);
        }
      });

      //Set moment language
      moment.locale(a.translations[a.currentLanguage].moment_lang);

      a.loginCheck(function(){

        //log('We are logged in!');

        a.refreshTempToken(function(){

          a.showView(window.location.hash);

          //Show app
          $('#loader').fadeOut(400, function(){
            $('#app').fadeIn(400, function(){
              a.showLoader = true;
            });
          });
        }, function(){
          a.logout();
        });

      }, function(){

        //Show start page
        $('#loader').delay(400).fadeOut(600, function(){
          //log('We are not logged in.');
          a.showView(window.location.hash);
          a.showLoader = true;
        });

      });

    });

  };

}

/* Init */
$(document).ready(function(){
  'use strict';

  $.ajaxSetup({
    cache: false
  });

  config.appUrl = getCurrentUrl();

  FastClick.attach(document.body);

  var app = new App();
  app.init();

  $(window).bind('hashchange', app.hashChanged);

  $(document).on('submit', '#form-forgot-password', function(e){
    e.preventDefault();
    app.forgotPassword();
  });

  $(document).on('submit', '#change-password', function(e){
    e.preventDefault();
    app.changePassword();
  });

  $(document).on('submit', '#change-email', function(e){
    e.preventDefault();
    app.changeEmail();
  });

  $(document).on('submit', '#form-login', function(e){
    e.preventDefault();

    var email = $(this).find('input[name="email"]').val();
    var password = $(this).find('input[name="password"]').val();
    var persistent = true; //$(this).find('input[name="remember-me"]:checked').length > 0; // true/false

    if(password !== '' && email !== '' && emailCheck.test(email)) {
      app.login(email, password, persistent);
    } else {
      app.message('login_validate_failed');
      //log('Validate error.');
    }

  });

  $(document).on('submit', '.form-discountcode', function(e){
    e.preventDefault();

    var testid = $(this).find('input[name="testid"]').val();
    var code = $(this).find('input[name="discountcode"]').val();

    app.saveOpenTests();

    app.submitDiscount(testid, code);

  });

  $(document).on('click', '.forgot-toggle', function(){
    $('#form-forgot-password').slideToggle();
  });

  $(document).on('click', '.match', function(){
    var id = $(this).data('matchid');
    app.openMatch(id);
  });

  $(document).on('click', '[role="change-language"]', function(){
    var locale = $(this).data('locale');
    app.changeLanguage(locale);
  });

  $(document).on('click', '[role="save-password"]', function(){
    app.changePassword();
  });

  $(document).on('click', '[role="save-privacy"]', function(){
    app.savePrivacy();
  });

  $(document).on('change', '#profile-privacy', function(e){
    if(!$(this).hasClass('editing')){
      $(this).addClass('editing');
    }
  });

  $(document).on('change', '#privacy-public', function(){
    if($(this).is(':checked')){
      $('#privacy-checkboxes').slideDown(200);
      $('#privacy-link').slideDown(200);
    } else {
      $('#privacy-checkboxes').slideUp(200);
      $('#privacy-link').slideUp(200);
    }
  });

  $(document).on('click', '[role="save-email"]', function(){
    app.changeEmail();
  });

  $(document).on('click', '[role="open-faq"]', function(){
    var faq = $(this).closest('.faqitem');
    if(!$(faq).hasClass('active')){
      $('.faqitem .text').stop(true, false).slideUp(200);
      $('.faqitem').removeClass('active');
      $(faq).addClass('active');
      $(faq).find('.text').stop(true, false).slideDown(200);
    } else {
      $('.faqitem .text').stop(true, false).slideUp(200);
      $('.faqitem').removeClass('active');
    }

  });

  $(document).on('click', '[role="open-registration"]', function(){
    $('#page-register .choice').fadeOut(400, function(){
      $('.fullscreenform').fadeIn(400);
    });
  });

  $(document).on('click', '.possibility', function(){
    var id = $(this).data('jobid');
    app.openJob(id);
  });

  $(document).on('click', '#button-back.close-job', function(e){
    e.preventDefault();
    e.stopPropagation();
    app.closeJob();
  });

  $(document).on('click', '#button-back.close-match', function(){
    app.closeMatch();
  });

  // Settings page

  $(document).on('click', '[role="logout"]', function(){
    app.logout();
  });

  $(document).on('click', '[role="linkedin-fetch"]', function(){
    app.fetchLinkedin();
  });

  $(document).on('click', '[role="remove-account"]', function(){
    app.removeAccount();
  });


  // Profile page

  window.onbeforeunload = function(e) {
    var lang = app.translations[app.currentLanguage];
    if ($('.editor.editing').length > 0) {
      return lang.alerts.unsaved;
    }
  };

  $(document).on('click', '[role="edit-start"]', function(){
    if (!$('.page-profile').hasClass('edit-open')){
      var lang = app.translations[app.currentLanguage];
      var that = this;

      $.each($(that).closest('.editor').find('.keywords[data-autocomplete]'), function(i, el){
        $(el).tagit({
          availableTags: lang.autocomplete[$(el).data('autocomplete')]
        });
      });

      $.each($(that).closest('.editor').find('input[data-autocomplete="degrees"]'), function(i, el){
        $(el).autocomplete({
          source: lang.autocomplete[$(el).data('autocomplete')]
        });
      });

      $(this).closest('.editor').addClass('editing');
      $(this).closest('.page-profile').addClass('edit-open');
    }
  });

  $(document).on('click', '[role="edit-cancel"]', function(){
    app.onLeaveCheck(false, function(){
      app.redrawAll(true);
    }, function(){
      //Cancel
    });
  });

  /* Positions */
  $(document).on('click', '[role="add-new-position"]', app.addNewPosition);
  $(document).on('click', '.experience [role="edit-save"]', app.savePositions);
  $(document).on('click', '[role="remove-position"]', function(){
    //var id = $(this).closest('.row.position').data('positionid');
    var pos = $(this).closest('.row.position');
    pos.slideUp(function(){
      if(pos.hasClass('new')){
        pos.remove();
      } else {
        pos.addClass('remove');
      }
    });
  });

  /* Education */
  $(document).on('click', '[role="add-new-education"]', app.addNewEducation);
  $(document).on('click', '.education [role="edit-save"]', app.saveEducation);
  $(document).on('click', '[role="remove-education"]', function(){
    var pos = $(this).closest('.row.education');
    pos.slideUp(function(){
      if(pos.hasClass('new')){
        pos.remove();
      } else {
        pos.addClass('remove');
      }
    });
  });

  /* Contact */
  $(document).on('click', '[role="add-new-link"]', app.addNewLink);
  $(document).on('click', '.contacts [role="edit-save"]', app.saveContact);
  $(document).on('click', '[role="remove-link"]', function(){
    var pos = $(this).closest('.row.link');
    pos.slideUp(function(){
      if(pos.hasClass('new')){
        pos.remove();
      } else {
        pos.addClass('remove');
      }
    });
  });

  /* Summary */
  $(document).on('click', '.summary [role="edit-save"]', app.saveSummary);

  /* Language */
  $(document).on('click', '[role="add-new-language"]', app.addNewLanguage);
  $(document).on('click', '[role="remove-language"]', function(){
    var pos = $(this).closest('.subrow.language');
    pos.slideUp(function(){
      if(pos.hasClass('new')){
        pos.remove();
      } else {
        pos.addClass('remove');
      }
    });
  });

  $(document).on('click', '.skills [role="edit-save"]', app.saveSkills);

  // ----

  $(document).on('change', 'fieldset.edit-time input[type="checkbox"]', function(){
    var parent = $(this).closest('fieldset');
    if($(this).prop('checked') === true){
      parent.addClass('not-ended');
    } else {
      parent.removeClass('not-ended');
    }
  });

  $(document).on('click', '.start-test', function(){
    var row = $(this).closest('form').find('.language');
    row.removeClass('error');
    var language = $(this).closest('form').find('[name="language"]').val();
    if(language !== ''){
      var instrument = $(this).data('instrument');
      var test = $(this).data('test');
      app.startTest(instrument, test, language);
    } else {
      $(row).addClass('error');
    }
  });

  $(document).on('click', '.reset-test', function(){
    var testid = $(this).data('testid');
    app.resetTest(testid);
  });

  $(document).on('click', '[role="buy-test"]', function(){
    var instrument = $(this).data('instrument');
    app.saveOpenTests();
    window.location = config.backendUrl + '/paytrail/payment?mazhr_token=' + settings.tempToken + '&instrument=' + instrument + '&return_url=' + encodeURIComponent(config.appUrl + '#tests');
  });

  $(document).on('click', '.reset-payment', function(){
    var testid = $(this).data('testid');
    app.resetPayment(testid);
  });

  $(document).on('click', '.discountcode .openform', function(){
    var that = this;
    $(this).slideUp(100, function(){
      $(that).closest('.discountcode').find('.form-discountcode').delay(100).slideDown(100);
    });
  });

  $(document).on('click', '[role="login-linkedin"]', function(){
    window.location = config.backendUrl + '/linkedin?mazhr_token=' + settings.tempToken + '&ok_url=' + encodeURIComponent(config.appUrl + '#signup') + '&fail_url=' + encodeURIComponent(config.appUrl + '#login');
  });

  $(document).on('click', '[role="register-linkedin"]', function(){
    window.location = config.backendUrl + '/linkedin?mazhr_token=' + settings.tempToken + '&ok_url=' + encodeURIComponent(config.appUrl + '#signup') + '&fail_url=' + encodeURIComponent(config.appUrl + '#start');
  });

  $(document).on('click', '.readmore', function(){
    $(this).hide();
    $(this).parent().parent().find('.more').show();
  });

  $(document).on('click', '.test .title', function(){
    var test = $(this).closest('.test');
    if(!$(test).hasClass('open')){
      $(test).find('.info').slideDown(200, function(){
        $(test).addClass('open');
      });
    } else {
      $(test).find('.info').slideUp(200, function(){
        $(test).removeClass('open');
      });
    }
  });

  $(document).on('click', '.start-loading', function(){
    //app.loading.start();
  });

});
