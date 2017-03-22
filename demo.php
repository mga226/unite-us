<?php

require 'autoload.php';

// Define the input.
$input = "Lead Chef, Chipotle, Denver, CO, 10, 15
Stunt Double, Equity, Los Angeles, CA, 15, 25
Manager of Fun, IBM, Albany, NY, 30, 40
Associate Tattoo Artist, Tit 4 Tat, Brooklyn, NY, 250, 275
Assistant to the Regional Manager, IBM, Scranton, PA, 10, 15
Lead Guitarist, Philharmonic, Woodstock, NY, 100, 200";

try {

    // Instantiate the CsvStringReformatter class
    $StringReformatter = new CsvStringReformatter;

    // Configure settings. This can be done at instantiation but I'm doing it here
    // for clarity.
    $StringReformatter->setInputColumns(['Title', 'Organization', 'City', 'State', 'Min', 'Max']);
    $StringReformatter->setHeader('All Opportunities');
    $StringReformatter->setRowTemplate('Title: {Title}, Organization: {Organization}, Location: {City}, {State}, Pay: {Min}-{Max}');
    $StringReformatter->setSortBy('Title');

    // Do the hard part!
    $output = $StringReformatter->reformat($input);

    println($output);
} catch (Exception $e) {
    println('Oh boy, something went wrong:');
    println(' > '.$e->getMessage());
}
