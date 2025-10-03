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
  // === Simple Capture Banner ===
(function () {
  if (!document.body) return;

  const STYLE_ID = 'capture-banner-style';
  if (!document.getElementById(STYLE_ID)) {
    const st = document.createElement('style');
    st.id = STYLE_ID;
    st.textContent = `
      .capture-banner {
        position: fixed;
        z-index: 99999;
        top: 18%;
        left: 50%;
        transform: translateX(-50%);
        max-width: 90vw;
        padding: 18px 22px;
        background: rgba(0,0,0,0.88);
        border: 2px solid #b30035; /* красно-бордовый контур */
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.45);
        text-align: center;
        pointer-events: none;
        opacity: 0;
        transition: opacity .25s ease, transform .25s ease;
      }
      .capture-banner.show {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
      }
      .capture-banner__title {
        font-size: 22px;
        font-weight: 800;
        color: #b30035;                /* красно-бордовый текст */
        -webkit-text-stroke: 1px #fff; /* белая окантовка текста */
        text-shadow: 0 2px 6px rgba(0,0,0,0.6);
        letter-spacing: .3px;
        line-height: 1.35;
      }
    `;
    document.head.appendChild(st);
  }

  function makeBanner(text) {
    let el = document.createElement('div');
    el.className = 'capture-banner';
    el.innerHTML = `<div class="capture-banner__title">${text}</div>`;
    document.body.appendChild(el);

    // показать
    requestAnimationFrame(() => el.classList.add('show'));

    // скрыть и удалить через 3.5 сек
    setTimeout(() => {
      el.classList.remove('show');
      setTimeout(() => el.remove(), 250);
    }, 3500);
  }

  // Глобальная функция для вызова из любого места
  window.showCaptureBanner = function (teamName, countryName) {
    const t = (teamName || 'Unknown Team').toString();
    const c = (countryName || 'Unknown Country').toString();
    makeBanner(`Team “${t}” captured “${c}”`);
  };

  // Также слушаем на всякий случай кастомное событие
  // window.dispatchEvent(new CustomEvent('ctf:capture', { detail: { team: 'A', country: 'B' } }))
  window.addEventListener('ctf:capture', (e) => {
    if (!e || !e.detail) return;
    window.showCaptureBanner(e.detail.team, e.detail.country);
  });
})();
// Показываем баннер при событии захвата
FB_CTF.subscribeActivity(function(activity) {
  if (activity.type === "capture") {
    // тут есть activity.team_name и activity.country_name
    if (activity.team_name && activity.country_name) {
      window.showCaptureBanner(activity.team_name, activity.country_name);
    }
  }
});
});
