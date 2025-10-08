<?hh // strict

require_once ($_SERVER['DOCUMENT_ROOT'].'/../vendor/autoload.php');

class ActivityModuleController extends ModuleController {
  public async function genRender(): Awaitable<:xhp> {

    /* HH_IGNORE_ERROR[1002] */
    SessionUtils::sessionStart();
    SessionUtils::enforceLogin();

    await tr_start();
    $activity_ul = <ul class="activity-stream"></ul>;

    list($all_activity, $config) = await \HH\Asio\va(
      ActivityLog::genAllActivity(),
      Configuration::gen('language'),
    );
    $language = $config->getValue();
    $activity_count = count($all_activity);
    $activity_limit = ($activity_count > 100) ? 100 : $activity_count;

    // Списки стран
    $investigation_countries = vec["Азербайджан", "Алжир", "Ангола", "Аргентина", "Армения", "Афганистан", "Бангладеш", "Беларусь", "Белиз", "Бенин", "Боливия", "Босния и Герцеговина", "Ботсвана", "Бразилия", "Бруней-Даруссалам", "Буркина-Фасо", "Бурунди", "Бутан", "Вануату", "Венесуэла", "Восточный Тимор", "Вьетнам", "Габон", "Гаити", "Гайана", "Гамбия", "Гана", "Гватемала", "Гвинея", "Гвинея-Бисау", "Гондурас", "Гренландия", "Грузия", "Джибути", "Доминиканская Республика", "Египет", "Замбия", "Западная Сахара", "Зимбабве", "Израиль", "Индия", "Индонезия", "Иордания", "Ирак", "Иран", "Йемен", "КНДР", "Казахстан", "Камбоджа", "Камерун", "Катар", "Кения", "Киргизия", "Китай", "Конго - Браззавиль", "Конго - Киншаса", "Косово", "Коста-Рика", "Кот-д’Ивуар", "Куба", "Кувейт", "Лаос", "Лесото", "Либерия", "Ливан", "Ливия", "Мавритания", "Мадагаскар", "Македония", "Малави", "Малайзия", "Мали", "Марокко", "Мексика", "Мозамбик", "Молдова", "Монголия", "Мьянма (Бирма)", "Намибия", "Непал", "Нигер", "Нигерия", "Никарагуа", "ОАЭ", "Оман", "Пакистан", "Палестинские территории", "Панама", "Папуа – Новая Гвинея", "Парагвай", "Перу", "Пуэрто-Рико", "Республика Корея", "Россия", "Руанда", "Сальвадор", "Саудовская Аравия", "Свазиленд", "Сербия", "Сирия", "Соломоновы о-ва", "Сомали", "Судан", "Суринам", "Сьерра-Леоне", "Таджикистан", "Таиланд", "Танзания", "Того", "Тринидад и Тобаго", "Тунис", "Туркменистан", "Турция", "Уганда", "Узбекистан", "Уругвай", "Фиджи", "Филиппины", "Фолклендские о-ва", "ЦАР", "Чад", "Чили", "Шри-Ланка", "Эквадор", "Экваториальная Гвинея", "Эритрея", "Эфиопия", "ЮАР", "Южный Судан", "Ямайка"];
    $capture_countries = vec["Австралия", "Австрия", "Албания", "Багамские о-ва", "Бельгия", "Болгария", "Великобритания", "Венгрия", "Германия", "Греция", "Дания", "Ирландия", "Исландия", "Испания", "Италия", "Канада", "Кипр", "Колумбия", "Латвия", "Литва", "Люксембург", "Нидерланды", "Новая Зеландия", "Новая Каледония", "Норвегия", "Польша", "Португалия", "Румыния", "Сенегал", "Словакия", "Словения", "Соединенные Штаты", "Тайвань", "Украина", "Финляндия", "Франция", "Французская Гвиана", "Французские Южные Территории", "Хорватия", "Черногория", "Чехия", "Швейцария", "Швеция", "Шпицберген и Ян-Майен", "Эстония", "Япония"];

    for ($i = 0; $i < $activity_limit; $i++) {
      $activity = $all_activity[$i];
      $subject = $activity->getSubject();
      $entity = $activity->getEntity();
      $ts = $activity->getTs();
      $visible = $activity->getVisible();
      if ($visible === false) {
        continue;
      }

      if (($subject !== '') && ($entity !== '')) {
        $class_li = '';
        $class_span = '';
        list($subject_type, $subject_id) =
          explode(':', $activity->getSubject());
        list($entity_type, $entity_id) = explode(':', $activity->getEntity());

        if ($subject_type === 'Team') {
          if (intval($subject_id) === SessionUtils::sessionTeam()) {
            $class_li = 'your-team';
            $class_span = 'your-name';
          } else {
            $class_li = 'opponent-team';
            $class_span = 'opponent-name';
          }
        }

        if ($entity_type === 'Country') {
          $formatted_entity = locale_get_display_region(
            '-'.$activity->getFormattedEntity(),
            $language,
          );
        } else {
          $formatted_entity = $activity->getFormattedEntity();
        }

        $team_node = <span class={'accent'}>{$activity->getFormattedSubject()}</span>;
        $country_node = <span class={'accent'}>{$formatted_entity}</span>;

        $action = $activity->getAction();
        $line = <x:frag />;

        if ($action === 'captured') {
          if (in_array($formatted_entity, $investigation_countries, true)) {
            $line =
              <x:frag>
                {$team_node} расследовала инцидент в организации из {$country_node}
              </x:frag>;
          } else if (in_array($formatted_entity, $capture_countries, true)) {
            $line =
              <x:frag>
                {$team_node} взломала организацию в {$country_node}
              </x:frag>;
          } else {
            $line =
              <x:frag>
                Команда {$team_node} выполнила задание {$country_node}
              </x:frag>;
          }
        } else if ($action === 'enabled') {
          $line =
            <x:frag>
              Задание {$country_node} было включено
            </x:frag>;
        } else if ($action === 'added') {
          $line =
            <x:frag>
              Задание {$country_node} было добавлено
            </x:frag>;
        } else {
          $line =
            <x:frag>
              {$team_node} {$action} {$country_node}
            </x:frag>;
        }

        $activity_ul->appendChild(
          <li class={$class_li}
              data-id={$activity->getId()}
              data-team={$activity->getFormattedSubject()}
              data-entity={$activity->getEntity()}
              data-action={$action}
              data-target={$formatted_entity}>
            [ {time_ago($ts)} ] {$line}
          </li>
        );
      } else {
        $activity_ul->appendChild(
          <li class={'opponent-team'}
              data-id={$activity->getId()}
              data-action={$activity->getAction()}>
            [ {time_ago($ts)} ]
            <span class={'opponent-name'}>
              {$activity->getFormattedMessage()}
            </span>
          </li>
        );
      }
    }

    return
      <div>
        <header class="module-header">
          <h6>{tr('Activity')}</h6>
        </header>
        <div class="module-content">
          <div class="fb-section-border">
            <div class="module-scrollable">
              {$activity_ul}
            </div>
          </div>
        </div>
      </div>;
  }
}

/* HH_IGNORE_ERROR[1002] */
$activity_generated = new ActivityModuleController();
$activity_generated->sendRender();