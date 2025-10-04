var Utils = require('./utils');
var FB_CTF = require('./fb-ctf');
var Admin = require('./admin');

var $ = require('jquery');
require('./plugins');

// Add the transitionend event to a global var
(function(window) {
  var transitions = {
    'transition': 'transitionend',
    'WebkitTransition': 'webkitTransitionEnd',
    'MozTransition': 'transitionend',
    'OTransition': 'otransitionend'
  },
      elem = document.createElement('div');

  for (var t in transitions) {
    if (typeof elem.style[t] !== 'undefined') {
      window.transitionEnd = transitions[t];
      break;
    }
  }
})(window);

function enableNavActiveState() {
  var page = Utils.getURLParameter('page');

  $('.fb-main-nav a').removeClass('active').filter(function() {
    var href = $(this).data('active');

    if (href === undefined || !href.indexOf || page === '') {
      return false;
    }
    return href.indexOf(page) > -1;
  }).addClass('active');
}

function enableAdminActiveState() {
  var page = Utils.getURLParameter('page');

  $('#fb-admin-nav li').removeClass('active').filter(function() {
    var href = $('a', this).attr('href').replace('#', '');

    if (href === undefined || !href.indexOf || page === '') {
      return false;
    }
    return href.indexOf(page) > -1;
  }).addClass('active');
}

function showCaptureBanner(text) {
  if (document.getElementById('capture-banner')) return;

  var banner = document.createElement('div');
  banner.id = 'capture-banner';
  banner.innerHTML = '<div class="inner">' + text + '</div>';
  document.body.appendChild(banner);

  banner.style.cssText = `
    position:fixed;top:0;left:0;right:0;bottom:0;
    background:rgba(33,33,33,0.85);
    color:#fff;font-size:48px;font-weight:bold;
    display:flex;align-items:center;justify-content:center;
    z-index:9999;
    font-family:'TT_Positive_Bold', sans-serif;
  `;
  banner.querySelector('.inner').style.cssText = `
    padding:30px 40px;
    border:4px solid #fff;
    background: #ff0000;
    text-transform:uppercase;
  `;

  setTimeout(function() {
    if (banner && banner.parentNode) banner.remove();
  }, 3000);
}

$(document).ready(function() {
  var page_location = window.location.pathname + window.location.search;
  if (window.innerWidth < 960 && page_location != '/index.php?page=mobile') {
    window.location = '/index.php?page=mobile';
  } else if (window.innerWidth < 960 && page_location == '/index.php?page=mobile') {
    setTimeout(function() {
      window.location = '/index.php';
    }, 2000);
  } else if (window.innerWidth >= 960 && page_location === '/index.php?page=mobile') {
    window.location = '/index.php';
  }

  FB_CTF.init();

  var section = $('body').data('section');
  if (section === 'pages') {
    enableNavActiveState();
  } else if (section === 'gameboard' || section === 'viewer-mode') {
    FB_CTF.gameboard.init();
  } else if (section === 'admin') {
    Admin.init();
    enableAdminActiveState();
  }

  $('body').trigger('content-loaded', {
    page: section
  });

  $(document).on('new-activity', function(e, activity) {
    if (activity.action === 'captured') {
      var text = 'Команда ' + activity.formatted_subject + ' выполнила задание ' + activity.formatted_entity;
      showCaptureBanner(text);
    }
  });
});
