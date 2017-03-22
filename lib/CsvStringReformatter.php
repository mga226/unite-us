<?php

/**
 * This class allows each row of a CSV string to be reformatted based on
 * configuration that you supply. Use it like this:
 *
 *    $reformatter = new CsvStringReformatter
 *
 *    // Map columns in the input to labels in the template
 *    $reformatter->setInputColumns(['name', 'species']);
 *
 *    // Specify the template string to be used for each row.
 *    $reformatter->setRowTemplate('{name} is a {species}.');
 *
 *    // Optional. Specify a field to the input rows by before reformatting.
 *    $reformatter->sortBy('name');
 *
 *    // Optional. Specify text for an overarching "header" row in the output.
 *    $reformatter->setHeader('Some Facts:'); // optional. if not supplied will
 *                                               be omitted from output
 *    $input = 'Rex, dog
 *    Mike, person';
 *    echo $reformatter->reformat($input);
 *
 *
 *    // Outputs like this:
 *    //   "Some Facts:
 *    //   Mike is a person.
 *    //   Rex is a dog."
 *
 * You can also pass configuration at instantiation like so:
 *
 *    $reformatter = new CsvStringReformatter([
 *       'input_columns' => ['name', 'species'],
 *       'row_template' => '{name} is a {species}.',
 *       'header' => 'Some facts:',
 *       'sort_by' => 'name'
 *    ]);
 *
 */
class CsvStringReformatter {

    /**
     * Array of field labels to by used when parsing CSV input
     *
     * @var string[]
     */
    protected $input_columns = array();

    /**
     * String to prepend to the output (optional)
     * @var string
     */
    protected $header;

    /**
     * An instance of the StringTemplateInterface, used to
     * parse input data. I did it this way because my parsing function
     * is extremely rudimentary and I wanted to be able to support easily
     * swapping it out.
     *
     * @var StringTemplateInterface
     */
    protected $row_template;

    /**
     * Name of field to sort by. Must be a member of ::input_columns,
     * which is enforced in the code.
     *
     * @var string
     */
    protected $sort_by;


    public function __construct(array $settings = array()) {
        $this->configure($settings);
     }

     /**
      * Sets configuration based on settings provided.
      *
      * @param  array[]  $settings
      */
     public function configure(array $settings) {

         if (isset($settings['input_columns'])) {
             $this->setInputColumns($settings['input_columns']);
         }

         if (isset($settings['header'])) {
             $this->setHeader($settings['header']);
         }

         if (isset($settings['row_template'])) {
             $this->setRowTemplate($settings['row_template']);
         }

         if (isset($settings['sort_by'])) {
             $this->setSortBy($settings['sort_by']);
         }
     }

     /**
      * Maps the columns in the input CSV to labels in the template.
      *
      * @param string[] $columns
      */
     public function setInputColumns(array $columns) {

         // Confirm that headers is an array of strings.
         foreach ($columns as $column) {
             if (!is_string($column)) {
                throw new Exception('Your columns must be strings!');
             }
         }

         // If this invalidates the 'sort_by' field, clear it.
         // (Now we won't sort unless we set a new 'sort_by' field.)
         if($this->sort_by && !in_array($this->sort_by, $columns)) {
             $this->sort_by = null;
         }

         // Set the columns
         $this->input_columns = $columns;
     }

     /**
      * Sets a generic header for the output (e.g. "All Opportunities").
      * Call without any arguments to clear out the existing header, if any.
      *
      * @param string $header
      */
     public function setHeader($header = null) {
         if (is_null($header)) {
             $this->header = $header;
             return;
         }
         if (!is_string($header)) {
             throw new Exception('Header should be a string.');
         }
         $this->header = $header;
     }

     /**
      * Takes a string, sets the new template on the current row_template
      *
      * @param string $template
      */
     public function setRowTemplate($template) {
        if (!$this->row_template) {
            $this->row_template = StringTemplateFactory::create();
        }
        $this->row_template->setTemplate($template);
     }

     /**
      * Sets the "sort_by" field. If the provided field is not included,
      * throw an exception.
      *
      * I'm ambivalent on whether an exception is appropriate here, but
      * we failure to enforce this could lead to unexpected and
      * difficult-to-debug behavior, so I'm going with it.
      *
      * @param string $field
      */
     public function setSortBy($field = null) {
         if (is_null($field)) {
             $this->sort_by = $field;
             return;
         }
         if (!is_string($field)) {
             throw new Exception('"Sort by" field should be a string.');
         }
         if (!in_array($field, $this->input_columns)) {
             throw new Exception('The field "'.$field.'" is not in the currently configured headers.');
         }
         $this->sort_by = $field;
     }

     /**
      * Takes an input string and returns the desired output format
      *
      * @param  string $input
      * @return  string
      */
     public function reformat($input) {

         // Make sure we're ready!
         if (!$this->input_columns) {
             throw new Exception('Cannot process CSV string without first setting input columns.');
         }
         if (!$this->row_template) {
             throw new Exception('Cannot process CSV string without first setting a row template.');
         }

         // Prepare the data...
         $data = $this->inputToArray($input);

         // Build the response...
         $response_lines = array();

         // Add the header, if any...
         if ($this->header) {
             $response_lines[] = $this->header;
         }

         // Populate the main content of the ouput string
         foreach ($data as $row) {
             $response_lines[] = $this->row_template->parse($row);
         }

         return implode($response_lines, "\n");
     }

     /**
      * Convert the input string into an array of associative arrays,
      * like this:
      *
      * array(
      *    array(
      *       'Title' => 'Cool guy',
      *       'City'  => 'Brooklyn',
      *       ...
      *    ),
      *    array(
      *       'Title' => 'Less cool guy',
      *       'City'  => 'Someplace else',
      *       ...
      *    ),
      * )
      *
      * @param  string $input
      * @return  array[] as above
      */
    protected function inputToArray($input) {
        $input_lines = explode("\n", trim($input));
        $data = array();
        foreach ($input_lines as $line) {
            $line = trim($line);
            if (!$line) {
                // If a line is blank for some reason, just skip it.
                continue;
            }
            $cells = preg_split('/\s*,\s*/', $line);
            if (count($cells) !== count($this->input_columns)) {
                 throw new Exception('Your input data is malformed :/');
            }
            $data[] = array_combine($this->input_columns, $cells);
        }

        $data = $this->sortData($data);

        return $data;
     }

     /**
      * Sort the provided data by the current "sort_by" field, if any.
      *
      * @param  array $data
      * @return  array
      */
     protected function sortData($data) {

         if ($sort_by = $this->sort_by) {
             usort($data, function($a, $b) use ($sort_by) {
                 if ($a[$sort_by] < $b[$sort_by]) {
                     return -1;
                 }
                 if ($b[$sort_by] < $a[$sort_by]) {
                     return 1;
                 }
                 return 0;
             });
         };
         return $data;
     }
}
