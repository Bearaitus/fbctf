<?hh // strict

class :fbbranding extends :x:element {
  category %flow;
  attribute string brandingText;

  protected string $tagName = 'fbbranding';

  protected function render(): XHPRoot {
    return
      <span class="branding-el">
        <span class="has-icon">{' '}{$this->:brandingText}</span>
      </span>;
  }
}
