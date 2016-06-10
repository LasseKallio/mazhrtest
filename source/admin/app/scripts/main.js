/*-----------------------------------------------------------------------------
                    APP INIT
-----------------------------------------------------------------------------*/
/**
 * Main 
 */
var moment = window.moment;
function App(){
    'use strict';
    this.view = window.location.hash === '' ? '#index' : window.location.hash;
    this.viewId = '';
    this.backend = '';
    this.loads = [];
    this.user = {'loggedIn':false};
    this.translations = {};
    this.lastOpenedElement = '';
    this.loading = {
        start: function() {
            App.loads.push(1);
            if(App.loads.length === 1) {
                $('body').addClass('blur');
            }
        },
        stop: function() {
            App.loads.pop();
            if(App.loads.length === 0) {
                $('body').removeClass('blur');
            }
        }
    };
    this.dataTableLanguage = {
        'sProcessing':   'Hetkinen...',
        'sLengthMenu':   'Näytä kerralla _MENU_ riviä',
        'sZeroRecords':  'Tietoja ei löytynyt',
        'sInfo':         'Näytetään rivit _START_ - _END_ (yhteensä _TOTAL_ )',
        'sInfoEmpty':    'Näytetään 0 - 0 (yhteensä 0)',
        'sInfoFiltered': '(suodatettu _MAX_ tuloksen joukosta)',
        'sInfoPostFix':  '',
        'sSearch':       'Etsi:',
        'sUrl':          '',
        'oPaginate': {
            'sFirst':    'Ensimmäinen',
            'sPrevious': 'Edellinen',
            'sNext':     'Seuraava',
            'sLast':     'Viimeinen'
        }
    };
}
/**
 * Init application
 *
 */
App.prototype.init = function(){
    'use strict';

    //configuration. Rest of it will come from the backend.
    if(document.location.host.indexOf('local') > -1) {
        App.backend = '//localhost/backend';
    } else if (document.location.host.indexOf('esmes') > -1){
        App.backend = '//testipenkki.esmes.fi/mzr/back';
    } else {
        App.backend = '//api.mazhr.com';
    }
    App.getUser(function(){
        App.getTranslations(function(){
            App.bindEvents();
            App.showView(App.view);
        });
    });
};
/**
 * Get user
 *
 */
App.prototype.getUser = function(callback){
    'use strict';
    App.loading.start();
    $.ajax(App.backend + '/admin/me',
    {
        type:'GET',
        xhrFields: {withCredentials: true},
        dataType : 'JSON',
    }).done(function(response){

        App.user.loggedIn = true;
        App.user.data = response.data;
    }).fail(function() {
    }).always(function(){
        App.loading.stop();
        if(typeof callback === 'function') {
            callback();
        }
    });
};
/**
 * Get language file
 *
 */
App.prototype.getTranslations = function(callback){
    'use strict';
    App.loading.start();
    $.ajax(App.backend + '/admin/language/fi',
    {
        type:'GET',
        xhrFields: {withCredentials: true},
        dataType : 'JSON',
    }).done(function(response){
        App.translations = response;
    }).fail(function() {
    }).always(function(){
        App.loading.stop();
        if(typeof callback === 'function') {
            callback();
        }
    });
};
/**
 * Show requested view
 *  
 */
App.prototype.showView = function(viewName){
    'use strict';

    App.lastOpenedElement = '';
    var requestedViewName = viewName;
    App.viewId = '';

    if(viewName.indexOf('/') !== -1 && viewName.indexOf('/') < viewName.length) {
        var viewParts = viewName.split('/');
        viewName = viewParts[0];
        App.viewId = viewParts[1];
    }

    // simple routing / redirecting
    var publicViews = ['#login'];
    if(App.user.loggedIn === false) {
        if(publicViews.indexOf(viewName) === -1){
            viewName = '#login';
        }
    } else {
        if(publicViews.indexOf(viewName) > -1){
            viewName = '#index';
        }
    }

    // navigate
    App.view = viewName;
    window.location.hash = requestedViewName;

    $('.nav-pills li').removeClass('active');
    $('.nav').show();

    switch(App.view) {
        case '#login':
            $('.nav').hide();
            App.loginView();
            break;
        case '#index':
            $('#nav-home').addClass('active');
            App.mainView();
            break;
        case '#users':
            $('#nav-users').addClass('active');
            if(App.viewId.length) {
                App.singleUserView();
            } else {
                App.usersView();
            }
            break;
        case '#profiles':
            $('#nav-profiles').addClass('active');
            App.profileView();
            break;
        case '#tests':
            $('#nav-tests').addClass('active');
            App.testView();
            break;
        case '#payments':
            $('#nav-payments').addClass('active');
            App.paymentsView();
            break;
    }
};
/**
 * Handle hash change
 */
App.prototype.hashChanged = function(){
    'use strict';
    if(window.location.hash !== App.view) {

        App.showView(window.location.hash);
    } else if (App.viewId !== '') {
        App.showView(window.location.hash);
    }
};
/**
 * Messaging to user
 */
App.prototype.message = function(content, type){
    'use strict';
    if(typeof type === 'undefined'){
        type = 'info';
    }
    // replace alert with cool messaging framework like http://smoke-js.com/
    window.alert(type + ': ' + content);
};
/**
 * "Render" a view with handlebars
 */
App.prototype.render = function(templateId, data){
    'use strict';
    var source   = $(templateId).html();
    var template = window.Handlebars.compile(source);
    var html = template(data);
    $('.content').html(html);
    $('.datetimepicker').datetimepicker({'lang': 'fi', 'format': 'Y-m-d H:i'});
    App.showLastOpened(App.lastOpenedElement);
};
/*-----------------------------------------------------------------------------
                    "VIEWS"
-----------------------------------------------------------------------------*/
/**
 * Login page
 */
App.prototype.loginView = function(){
    'use strict';
    var data = {};
    App.render('#login-template', data);
};
/**
 * Main page
 */
App.prototype.mainView = function(){
    'use strict';
    var data = {};
    App.render('#main-template', data);
};
/**
 * Users page
 */
App.prototype.usersView = function(){
    'use strict';
    var users = [];
    var tableData = [];
    $.ajax(App.backend + '/admin/users',
    {
        type:'GET',
        xhrFields: {withCredentials: true},
        dataType : 'JSON'
    }).done(function(response){
        users = response.data;
        App.render('#users-template');
        
        $.each(users, function( index, user ) {
            var newRow = [user.id, user.first, user.last, user.email, user.candidate_id];
            tableData.push(newRow);
        });
        console.log(tableData);
        $('#user-table').DataTable( {
            data: tableData,
            language: App.dataTableLanguage
        });
        
    }).fail(function() {
    }).always(function(){
        App.loading.stop();
    });
};
/**
 * Payments view
 */
App.prototype.paymentsView = function(){
    'use strict';
    var payments = [];
    var tableData = [];
    $.ajax(App.backend + '/admin/payments',
    {
        type:'GET',
        xhrFields: {withCredentials: true},
        dataType : 'JSON'
    }).done(function(response){
        payments = response.data;
        App.render('#payments-template');
        
        $.each(payments, function( index, log ) {
            var userId = "<a style=\"color: white; text-decoration: underline;\" href=\"#users/" + log.user_id + "\">" + log.user_id + "</a>";
            var newRow = [log.id, userId, moment(log.updated_at).format('D.M.YYYY H:mm'), log.product_id, log.product_name, log.order_number, log.sum, log.code, log.status];
            tableData.push(newRow);
        });

        var table = $('#payment-table').DataTable( {
            data: tableData,
            language: App.dataTableLanguage
        });

        $('#payments-from').datepicker({
            defaultDate: '-1w',
            changeMonth: true,
            numberOfMonths: 2,
            dateFormat: 'd.m.yy',
            onClose: function( selectedDate ) {
                $('#payments-to').datepicker('option', 'minDate', selectedDate );
            }
        });
        // muista jaken possibilities
        $('#payments-to').datepicker({
            defaultDate: '+1w',
            changeMonth: true,
            numberOfMonths: 2,
            dateFormat: 'd.m.yy',
            onClose: function( selectedDate ) {
                $('#payments-from').datepicker('option', 'maxDate', selectedDate );
            }
        });

        $('#payments-to, #payments-from').bind('change', function(){
            $.fn.dataTableExt.afnFiltering.push(function (oSettings, aData) {

                var to = $('#payments-to').val() ? $('#payments-to').val() : '1.1.3000';
                var from = $('#payments-from').val() ? $('#payments-from').val() : '1.1.1970';

                var timeParts = aData[1].split(' ');
                var date = timeParts[0];

                return ((moment(from, 'D.M.YYYY') <= moment(date, 'D.M.YYYY')) && (moment(to, 'D.M.YYYY') >= moment(date, 'D.M.YYYY')));
            });
            table.draw();
        });

    }).fail(function() {
    }).always(function(){
        App.loading.stop();
    });
};
/**
 * Single user
 */
App.prototype.singleUserView = function(){
    'use strict';
    var data = {};
    var tableData = [];
    var summary = {};
    $.ajax(App.backend + '/admin/user/' + App.viewId,
    {
        type:'GET',
        xhrFields: {withCredentials: true},
        dataType : 'JSON'
    }).done(function(response){
        data.user = response.data;
        // user image
        if(data.user.image) {
            data.user.image = App.backend + '/uploads/' + data.user.image;
        }
        // education level
        if(data.user.education_level) {
            data.user.education_level = App.translations.education_levels[data.user.education_level];
        }

        // building a summaries from extras
        summary.card = '';
        summary.interest = '';
        summary.location = '';

        function checkValue(value, translation, target) {
            if(typeof value !== 'undefined' && value.value) {
                var separator = ', ';
                if(!target.length) {
                    separator = '';
                }
                return target + separator + translation;
            }
        }

        var e = response.data.extras;
        var p = App.translations.profile;

        summary.card = checkValue(e.card_duuni, p.form.cards.duuni, summary.card);
        summary.card = checkValue(e.card_sanssi, p.form.cards.sanssi, summary.card);

        summary.location = checkValue(e.location_abroad, p.form.locations.abroad, summary.location);
        summary.location = checkValue(e.location_home, p.form.locations.home, summary.location);

        summary.interest = checkValue(e.summary_evenings_weekends, p.form.interests.evenings_weekends, summary.interest);
        summary.interest = checkValue(e.summary_fulltime, p.form.interests.fulltime, summary.interest);
        summary.interest = checkValue(e.summary_parttime, p.form.interests.parttime, summary.interest);
        summary.interest = checkValue(e.summary_shifts, p.form.interests.shifts, summary.interest);

        // translate languages
        if(typeof data.user.skills.languages === 'object') {
            var l = data.user.skills.languages;
            $.each(l, function( index ) {
                l[index].key = p.languages[l[index].key].name;
                l[index].value = p.language_skill_ratings[l[index].value];
            });
        }
        // translate experience
        if(typeof data.user.skills.experience === 'object') {
            var ex = data.user.skills.experience;
            $.each(ex, function( index ) {
                ex[index].key = p.form.experience_fields[ex[index].key];
                if(ex[index].value > 1) {
                    ex[index].value += ' ' + App.translations.date_years;
                } else {
                    ex[index].value += ' ' + App.translations.date_year_single;
                }
            });
        }

        // main experience
        if(typeof data.user.skills.primary_experience_type === 'object' && typeof data.user.skills.primary_experience_years === 'object') {
            var primary_experience = {};
            var etype = data.user.skills.primary_experience_type;
            var eyears = data.user.skills.primary_experience_years;
            if(etype.value && eyears.value) {
                primary_experience.key = p.primary_experience_types[etype.value];
                if(eyears.value > 1) {
                    primary_experience.value = eyears.value + ' ' + App.translations.date_years;
                } else {
                    primary_experience.value = eyears.value + ' ' + App.translations.date_year_single;
                }
            }
            data.user.skills.primary_experience = primary_experience;
        }

        data.user.summary = summary;
        data.tr = App.translations;
        App.render('#user-template', data);

        $.each(response.data.payment_history, function( index, log ) {
            var newRow = [log.id, moment(log.updated_at).format('D.M.YYYY H:mm'), log.product_id, log.product_name, log.order_number, log.sum, log.status];
            tableData.push(newRow);
        });

        $('#user-payment-table').DataTable( {
            data: tableData,
            language: App.dataTableLanguage
        });

    }).fail(function() {
    }).always(function(){
        App.loading.stop();
    });
};
/**
 * Profiles
 */
App.prototype.profileView = function(){
    'use strict';
    var profiles = [];
    $.ajax(App.backend + '/admin/profile',
    {
        type:'GET',
        xhrFields: {withCredentials: true},
        dataType : 'JSON'
    }).done(function(response){
        profiles = response.data;
        App.render('#profiles-template', profiles);
    }).fail(function() {
    }).always(function(){
        App.loading.stop();
    });
};
/**
 * Tests
 */
App.prototype.testView = function(){
    'use strict';
    var tests = [];
    $.ajax(App.backend + '/admin/tests',
    {
        type:'GET',
        xhrFields: {withCredentials: true},
        dataType : 'JSON'
    }).done(function(response){
        tests = response.data;
        App.render('#tests-template', tests);
    }).fail(function() {
    }).always(function(){
        App.loading.stop();
    });
};
/*-----------------------------------------------------------------------------
                    ACTIONS
-----------------------------------------------------------------------------*/
/**
 * Login action
 */
App.prototype.login = function(){
    'use strict';
    var email = $('#login-form input[name="email"]').val();
    var password = $('#login-form  input[name="password"]').val();
    //var userData = {};
    App.loading.start();
    $.ajax(App.backend + '/admin/login',
    {
        type:'POST',
        xhrFields: {withCredentials: true},
        dataType : 'JSON',
        data: {
            'email': email,
            'password': password
        }
    }).done(function(response){
        App.user.loggedIn = true;
        App.user.data = response.data;
        App.showView('#index');
    }).fail(function(response) {
        App.message(response.responseText, 'warning');
    }).always(function(){
        App.loading.stop();
    });
};
/**
 * Logout action
 */
App.prototype.logout = function(){
    'use strict';
    App.loading.start();
    $.ajax(App.backend + '/admin/logout',
    {
        type:'POST',
        xhrFields: {withCredentials: true},
        dataType : 'JSON'
    }).done(function(){
        App.user.loggedIn = false;
        App.user.data = {};
        App.showView('#start');
    }).fail(function(response) {
        App.message(response.responseText);
    }).always(function(){
        App.loading.stop();
    });
};
/**
 * Remove profession code from profile
 */
App.prototype.removeProfessionCode = function(){
    'use strict';
    App.loading.start();
    var button = $(this);
    var removeId = button.data('codeId');
    $.ajax(App.backend + '/admin/profile/' + removeId,
    {
        type:'DELETE',
        xhrFields: {withCredentials: true}
    }).done(function(){
        button.parent().remove();
    }).fail(function() {
    }).always(function(){
        App.loading.stop();
    });
};
/**
 * Add profession code to profile
 */
App.prototype.addProfessionCode = function(){
    'use strict';
    App.loading.start();
    var code = $(this).prev().val();
    var profileId = $(this).data('profileId');
    var profiles = [];
    $.ajax(App.backend + '/admin/profile/id/' + profileId + '/code/' + code,
    {
        type:'POST',
        xhrFields: {withCredentials: true},
        dataType : 'JSON'
    }).done(function(response){
        profiles = response.data;
        App.render('#profiles-template', profiles);
    }).fail(function() {
    }).always(function(){
        App.loading.stop();
    });
};

/**
 * Save profile
 */
App.prototype.saveProfile = function(){
    'use strict';
    App.loading.start();
    var id = $(this).data('profile-id');
    var competence = $('#profile-'+ id + ' input[name="competence"]').val();
    var model = $('#profile-'+ id + ' input[name="model"]').val();
    var profiles = [];
    $.ajax(App.backend + '/admin/profile',
    {
        type:'POST',
        xhrFields: {withCredentials: true},
        dataType : 'JSON',
        data: {
            id: id,
            competence: competence,
            model: model
        }
    }).done(function(response){
        profiles = response.data;
        App.render('#profiles-template', profiles);
    }).fail(function(response) {
        App.message(response.responseText);
    }).always(function(){
        App.loading.stop();
    });
};

/**
 * Update tests
 */
App.prototype.updateTest = function(){
    'use strict';
    App.loading.start();
    var id = $(this).data('test-id');
    var price = $(this).parent().find('input[name="value"]').val();
    var second_price = $(this).parent().find('input[name="second_value"]').val();
    var data = {'price': price, 'second_price': second_price};
    var tests = [];
    $.ajax(App.backend + '/admin/test/id/' + id,
    {
        type:'POST',
        xhrFields: {withCredentials: true},
        dataType : 'JSON',
        data: JSON.stringify({'data':data}),
        processData: false
    }).done(function(response){
        tests = response.data;
        App.render('#tests-template', tests);
    }).fail(function(response) {
        App.message(response.responseText);
    }).always(function(){
        App.loading.stop();
    });
};

/**
 * Save discount code
 */
App.prototype.saveDiscount = function(){
    'use strict';
    App.loading.start();
    var test_id = $(this).data('test-id');

    var data = {};
    data.test_id = test_id;
    if($(this).hasClass('remove-discount')) {
        data.status = 2;
    }
    else {
        data.status = 1;
        data.code = $(this).closest('.row').find('input[name="code"]').val();
        data.price = $(this).closest('.row').find('input[name="price"]').val();
        data.start = $(this).closest('.row').find('input[name="start"]').val();
        data.end = $(this).closest('.row').find('input[name="end"]').val();
        data.usage_limit = $(this).closest('.row').find('input[name="usage_limit"]').val();
    }

    if(typeof $(this).closest('.row').find('input[name="discount_id"]') !== 'undefined') {
        data.id = $(this).closest('.row').find('input[name="discount_id"]').val();
    }

    var tests = [];
    $.ajax(App.backend + '/admin/test/id/'+ test_id +'/discount',
    {
        type:'POST',
        xhrFields: {withCredentials: true},
        dataType : 'JSON',
        data: JSON.stringify({'data':data}),
        processData: false
    }).done(function(response){
        tests = response.data;
        App.render('#tests-template', tests);
    }).fail(function(response) {
        App.message(response.responseText);
    }).always(function(){
        App.loading.stop();
    });
};

/**
 * Fold / unfold
 */
App.prototype.foldToggle = function(){
    'use strict';
    var element = $(this);
    App.lastOpenedElement = element.attr('id');

    if(!element.hasClass('open')) {
        $('.row-extra').hide();
        $('.open').removeClass('open');
        element.addClass('open');
        element.find('.row-extra').fadeIn();
        element.next('.row-extra').fadeIn();
    }
};

App.prototype.showLastOpened = function(foldableId) {
    'use strict';
    if(foldableId !== '') {
        var foldable = $('#' + foldableId);
        foldable.addClass('open');
        foldable.find('.row-extra').show();
        foldable.next('.row-extra').show();
        $(window).scrollTop(foldable.offset().top);
    }
};
/**
 * Update user email 
 */
App.prototype.updateUserEmail = function(){
    'use strict';
    App.loading.start();
    var data = {'user_id': $(this).data('user-id'), 'email':$(this).prev('input').val()};
    $.ajax(App.backend + '/admin/user',
    {
        type:'POST',
        xhrFields: {withCredentials: true},
        dataType : 'JSON',
        data: JSON.stringify({'data':data}),
        processData: false
    }).done(function(){
        App.singleUserView();
    }).fail(function(response) {
        App.message(response.responseText);
    }).always(function(){
        App.loading.stop();
    });
};
/**
 * Show user from user table 
 */
App.prototype.userTableClick = function(){
    'use strict';
    var userid = $(this).children().first().html();
    window.location.href = '#users/' + userid;
};
/**
 * Send password 
 */
App.prototype.sendPassword = function(){
    'use strict';
    var email = $(this).data('email');
    App.loading.start();
    $.ajax(App.backend + '/admin/password',
    {
        type:'POST',
        xhrFields: {withCredentials: true},
        dataType : 'JSON',
        data: {'email' : email}
    }).done(function(){
        App.message('Salasanaviesti lähetetty!');
    }).fail(function(response) {
        App.message(response.responseText);
    }).always(function(){
        App.loading.stop();
    });
};
/**
 * Csv download
 */
App.prototype.csvDownload = function(){
    'use strict';
    window.location = App.backend + '/admin/payments/csv';

};
/**
 * Remove user 
 */
App.prototype.removeUser = function(){
    'use strict';
    App.loading.start();
    var data = {'user_id': $(this).data('user-id')};
    $.ajax(App.backend + '/admin/user/remove',
    {
        type:'POST',
        xhrFields: {withCredentials: true},
        dataType : 'JSON',
        data: JSON.stringify({'data':data}),
        processData: false
    }).done(function(){
        App.message('Käyttäjä poistettu!');
    }).fail(function(response) {
        App.message(response.responseText);
    }).always(function(){
        App.loading.stop();
    });
};

/*-----------------------------------------------------------------------------
                    EVENTS
-----------------------------------------------------------------------------*/
App.prototype.bindEvents = function(){
    'use strict';
    $(window).bind('hashchange', App.hashChanged);
    $('.container').on('click', '#login-form button', App.login);
    $('.container').on('click', '.logout', App.logout);
    $('.container').on('click', '.remove-profession-code', App.removeProfessionCode);
    $('.container').on('click', '.add-profession-code', App.addProfessionCode);
    $('.container').on('click', '.update-test', App.updateTest);
    $('.container').on('click', '.save-profile', App.saveProfile);
    $('.container').on('click', '.save-discount, .remove-discount', App.saveDiscount);
    $('.container').on('click', '.foldable', App.foldToggle);
    $('.container').on('click', '.update-email', App.updateUserEmail);
    $('.container').on('click', '#user-table tbody tr', App.userTableClick);
    $('.container').on('click', '.send-password', App.sendPassword);
    $('.container').on('click', '.csv-download', App.csvDownload);
    $('.container').on('click', '.remove-user', App.removeUser);
};

/*-----------------------------------------------------------------------------
                    DOCUMENT READY
-----------------------------------------------------------------------------*/
var App = new App();
$(document).ready(function(){
    'use strict';
    App.init();
});