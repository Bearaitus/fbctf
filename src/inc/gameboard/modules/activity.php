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

    // Country lists
    $investigation_countries = vec["Azerbaijan", "Algeria", "Angola", "Argentina", "Armenia", "Afghanistan", "Bangladesh", "Belarus", "Belize", "Benin", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei Darussalam", "Burkina Faso", "Burundi", "Bhutan", "Vanuatu", "Venezuela", "Timor-Leste", "Vietnam", "Gabon", "Haiti", "Guyana", "Gambia", "Ghana", "Guatemala", "Guinea", "Guinea-Bissau", "Honduras", "Greenland", "Georgia", "Djibouti", "Dominican Republic", "Egypt", "Zambia", "Western Sahara", "Zimbabwe", "Israel", "India", "Indonesia", "Jordan", "Iraq", "Iran", "Yemen", "North Korea", "Kazakhstan", "Cambodia", "Cameroon", "Qatar", "Kenya", "Kyrgyzstan", "China", "Congo - Brazzaville", "Congo - Kinshasa", "Kosovo", "Costa Rica", "Côte d'Ivoire", "Cuba", "Kuwait", "Laos", "Lesotho", "Liberia", "Lebanon", "Libya", "Mauritania", "Madagascar", "Macedonia", "Malawi", "Malaysia", "Mali", "Morocco", "Mexico", "Mozambique", "Moldova", "Mongolia", "Myanmar", "Namibia", "Nepal", "Niger", "Nigeria", "Nicaragua", "United Arab Emirates", "Oman", "Pakistan", "Palestinian Territories", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Puerto Rico", "South Korea", "Russia", "Rwanda", "El Salvador", "Saudi Arabia", "Swaziland", "Serbia", "Syria", "Solomon Islands", "Somalia", "Sudan", "Suriname", "Sierra Leone", "Tajikistan", "Thailand", "Tanzania", "Togo", "Trinidad and Tobago", "Tunisia", "Turkmenistan", "Turkey", "Uganda", "Uzbekistan", "Uruguay", "Fiji", "Philippines", "Falkland Islands", "Central African Republic", "Chad", "Chile", "Sri Lanka", "Ecuador", "Equatorial Guinea", "Eritrea", "Ethiopia", "South Africa", "South Sudan", "Jamaica"];
    $capture_countries = vec["Australia", "Austria", "Albahhama", "Belgium", "Bulgaria", "United Kingdom", "Hungary", "Germany", "Greece", "Denmark", "Ireland", "Iceland", "Spain", "Italy", "Canada", "Cyprus", "Colombia", "Latvia", "Lithuania", "Luxembourg", "Netherlands", "New Zealand", "New Caledonia", "Norway", "Poland", "Portugal", "Romania", "Senegal", "Slovakia", "Slovenia", "United States", "Taiwan", "Ukraine", "Finland", "France", "French Guiana", "French Southern Territories", "Croatia", "Montenegro", "Czech Republic", "Switzerland", "Sweden", "Svalbard and Jan Mayen", "Estonia", "Japan"];

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
                {$team_node} {tr('investigated incident in')} {$country_node}
              </x:frag>;
          } else if (in_array($formatted_entity, $capture_countries, true)) {
            $line =
              <x:frag>
                {$team_node} {tr('hacked organization in')} {$country_node}
              </x:frag>;
          } else {
            $line =
              <x:frag>
                {tr('Team')} {$team_node} {tr('completed')} {$country_node}
              </x:frag>;
          }
        } else if ($action === 'enabled') {
          $line =
            <x:frag>
              {$country_node} {tr('was enabled')}
            </x:frag>;
        } else if ($action === 'added') {
          $line =
            <x:frag>
              {$country_node} {tr('was added')}
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