/*! handmade by esmes.fi */

//Linter preferences
/*global $, FastClick, moment, sweetAlert*/
/*jslint node: true*/
/*jshint unused:false, camelcase:false */

// ### Settings and such
window.onunload = function(){};

var debug = true;

var config = {
  appUrl: null,
  //backendUrl: '//testipenkki.esmes.fi/mzr/back',
  backendUrl: '//api.mazhr.dev',
  confirmButtonColor: '#00b0a0',
  destructiveButtonColor: '#c44040'
};

// ### IE Detect
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

// ### Handlebars helpers
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

function getStarsFromScore(original_score){
  'use strict';
  original_score = parseInt(original_score, 10);
  var score = (original_score+1)*0.5;
  return getStars(score);
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

//Load settings
var settings = loadData(config.datastore);
if(settings === null){
  settings = {
    language: 'fi_fi'
  };
}

// ### App

function App(){
  'use strict';
  var a = this; //Easier to type, and we need a reference other than 'this' inside xhr callbacks.

  //Variables
  a.currentLanguage = settings.language ? settings.language : 'fi_fi';
  a.translations = {};

  //Methods
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

  a.getApiUrl = function(endpoint){
    var apiUrl = config.backendUrl + '/';
    apiUrl += endpoint;
    return apiUrl;
  };

  a.apiCall = function(endpoint, data, success, failure, type, sendRaw){
    var headers = {};

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
      confirmButtonColor: config.confirmButtonColor,
      closeOnConfirm: false
    },
    function(){
      window.location.href = 'https://www.mazhr.com';
    });

  };

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

  a.loadData = function(profileId){

    var data = {lang: a.translations[a.currentLanguage]};
    var userdata;

    $.when(
      a.apiCall('profile/' + profileId , {}, function(response){
        userdata = response.data;
      }, function(){
        a.message('profileid_error');
        $('#loader').fadeOut(400, function(){});
      }, 'GET')
    ).then(function(){

      var hidden_blocks = {
        extras: (userdata.extras === undefined),
        education: (userdata.education === undefined),
        skills: (userdata.skills === undefined),
        workhistory: (userdata.workhistory === undefined)
      };

      data.hidden_blocks =  hidden_blocks;

      console.log(hidden_blocks);

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

      if (userdata.extras !== undefined){

        data.show_extras = true;

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

      if(userdata.extras !== undefined){
        summary.education_level = {id: userdata.education_level, name: data.lang.education_levels[userdata.education_level]};
      }

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

      var experience_shown = false;
      var primary_experience;

      if(userdata.skills !== undefined){

        experience.leadership = findData(userdata.skills.experience, 'leadership');
        experience.superior = findData(userdata.skills.experience, 'superior');
        experience.entrepreneur = findData(userdata.skills.experience, 'entrepreneur');
        experience.abroad = findData(userdata.skills.experience, 'abroad');


        $.each(experience, function(i, exp){
          if(exp.show === true){
            experience_shown = true;
            return false;
          }
        });

        //log(experience);
        //log('experience_shown: '+experience_shown);

        primary_experience = {shown: false, key: '', value: '', years_value: '', years: ''};

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
        languages: (userdata.skills !== undefined && userdata.skills.languages !== undefined && userdata.skills.languages.length > 0) ? userdata.skills.languages : [],
        experience: experience,
        experience_shown: experience_shown,
        primary_experience: primary_experience
      };

      if(userdata.skills !== undefined){

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

      }

      $.each(data.user.experience, function(i, experience){
        experience.name = data.lang.profile.form.experience_fields[experience.key];
        experience.keywords_array = (experience.keywords !== undefined && experience.keywords !== '') ? experience.keywords.split(',') : [];

        experience.years = parseInt(experience.value, 10) === 1 ? experience.value+' '+data.lang.date_year_single : experience.value+' '+data.lang.date_years;

      });

      //log(data.user);

      $.each(data.user.positions, a.parsePosition);
      $.each(data.user.education, a.parseEducation);

      a.render('#menu-template', data, '#menu');
      a.render('#content-sidebar-profile-template', data, '#content-sidebar');
      a.render('#content-page-profile-template', data, '#content-page');
      $('#content-page input[type="text"]').placeholder();

      if(userdata.tests !== undefined && userdata.tests.length) {

        var sorted = [];
        checkAndAdd(102, userdata.tests, sorted);
        checkAndAdd(201, userdata.tests, sorted);
        checkAndAdd(302, userdata.tests, sorted);
        checkAndAdd(301, userdata.tests, sorted);
        checkAndAdd(357, userdata.tests, sorted);
        checkAndAdd(315, userdata.tests, sorted);

        $.each(sorted, function(key, value){
          if(/*response.data[value.instrument_id] !== undefined &&*/ value.score_key !== '' && value.score_key !== null){
            //var test_name = response.data[value.instrument_id].name;

            var test_name = data.lang.tests.testids['test-'+value.instrument_id];

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

      $('#content-page').show();
      $('#content-sidebar').show();

      //Show app
      $('#loader').fadeOut(400, function(){
        $('#app').fadeIn(400, function(){});
      });

    });
  };

  a.init = function(){

    var ieVersion = getInternetExplorerVersion();

    if(ieVersion !== -1 && ieVersion < 10){
      log('Old IE');
      $('html').addClass('old-ie');
    }

    if(ieVersion !== -1){
      log('Adding IE class to html');
      $('html').addClass('ie');
    }

    $(window).bind('hashchange', function(){
      document.location.reload(true);
    });

    //Load translations and ping online status
    $.when(
      a.loadTranslation('fi_fi', '../languages/finnish.json'),
      a.loadTranslation('en_en', '../languages/english.json'),
      a.loadTranslation('sv_se', '../languages/swedish.json')
    ).then(function(){

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

      //Set moment language
      moment.locale(a.translations[a.currentLanguage].moment_lang);

      if(window.location.hash !== ''){
        var profileId = window.location.hash.substring(1);
        a.loadData(profileId);

      } else {
        $('#loader').fadeOut(400, function(){});
        a.message('profileid_error');
      }



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

});
