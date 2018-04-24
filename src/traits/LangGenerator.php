<?php
namespace IchikawaYoshihiro\ReverseScaffoldGenerator\Traits;

trait LangGenerator {

  public function generateLang()
  {
      $langs = [
          'crud' => [
              'save' => 'Save',
              'create' => 'Create',
              'show' => 'Show',
              'edit' => 'Edit',
              'delete' => 'Delete',
              'confirm' => 'Are you sure?',
              'created' => 'Created',
              'updated' => 'Updated',
              'deleted' => 'Deleted',
              'required' => 'Required',
          ],
          $this->valiable_name => $this->buildLangColumn(),
      ];
      $path = resource_path('lang/en/message.php');

      $replaces = [
          'DummyLangs' => var_export($this->mergeLang($path, $langs), true),
      ];
      $stub = static::getStubFile('message.stub');

      $this->fileGenerate($stub, $replaces, $path);
  }

  protected function buildLangColumn()
  {
      $name  = static::toTitle($this->valiable_name);
      $names = static::toTitle($this->valiables_name);

      $base = [
          'id' => 'ID',
          'action' => 'Action',
          'create' => "Create new {$name}",
          'show' => "Show {$name}",
          'edit' => "Edit {$name}",
          'list' => "List of {$names}",
      ];

      $columns = $this->fillableFields()->pluck('Field')->reduce(function($carry, $item) {
          $carry[$item] = static::toTitle($item);
          return $carry;
      }, []);

      return array_replace_recursive ($base, $columns);
  }
  protected static function toTitle($str)
  {
      return title_case(str_replace(['_', '-'], ' ', $str));
  }


  protected static function mergeLang($path, $langs)
  {
      $old = file_exists($path) ? require($path) : [];

      return array_replace_recursive($old, $langs);
  }
}
