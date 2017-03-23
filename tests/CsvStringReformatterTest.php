<?php

use PHPUnit\Framework\TestCase;

/**
 * @covers CsvStringReformatter
 */
final class CsvStringReformatterTest extends TestCase {

    protected $input = "hi, mike\nhey, michael";

    public function testSimpleCase() : void
    {

        $Reformatter = new CsvStringReformatter([
            'input_columns' => ['greeting', 'name'],
            'row_template' => '{greeting}, my name is {name}',
        ]);

        $input = $this->input;
        $expected_output = "hi, my name is mike\nhey, my name is michael";

        $this->assertEquals(
            $Reformatter->reformat($this->input),
            $expected_output
        );
    }

    public function testSimpleCaseWithSort() : void
    {

        $Reformatter = new CsvStringReformatter([
            'input_columns' => ['greeting', 'name'],
            'row_template'  => '{greeting}, my name is {name}',
            'sort_by'       => 'name',
        ]);

        $input = $this->input;
        $expected_output = "hey, my name is michael\nhi, my name is mike";

        $this->assertEquals(
            $Reformatter->reformat($this->input),
            $expected_output
        );
    }

    public function testSimpleCaseWithHeader() : void
    {

        $Reformatter = new CsvStringReformatter([
            'input_columns' => ['greeting', 'name'],
            'row_template'  => '{greeting}, my name is {name}',
            'header'        => 'Bore em gypsum',
        ]);

        $input = $this->input;
        $expected_output = "Bore em gypsum\nhi, my name is mike\nhey, my name is michael";

        $this->assertEquals(
            $Reformatter->reformat($this->input),
            $expected_output
        );
    }

    public function testNoTemplateThrowsException(): void
    {
        $Reformatter = new CsvStringReformatter([
            'input_columns' => ['greeting', 'name'],
        ]);

        $input = $this->input;
        $this->expectException(Exception::class);

        $Reformatter->reformat($this->input);
    }

    public function testNoInputColumnsThrowsException(): void
    {
        $Reformatter = new CsvStringReformatter([
            'row_template' => '{greeting}, my name is {name}'
        ]);

        $input = $this->input;
        $this->expectException(Exception::class);

        $Reformatter->reformat($this->input);
    }

    public function testBadSortFieldThrowsException(): void
    {
        $Reformatter = new CsvStringReformatter([
            'input_columns' => ['greeting', 'name'],
        ]);
        $this->expectException(Exception::class);
        $Reformatter->setSortBy('beep');
    }

    public function testExampleCaseWorksCorrectly() : void
    {

        $input = "Lead Chef, Chipotle, Denver, CO, 10, 15
Stunt Double, Equity, Los Angeles, CA, 15, 25
Manager of Fun, IBM, Albany, NY, 30, 40
Associate Tattoo Artist, Tit 4 Tat, Brooklyn, NY, 250, 275
Assistant to the Regional Manager, IBM, Scranton, PA, 10, 15
Lead Guitarist, Philharmonic, Woodstock, NY, 100, 200";

        $expected_output = "All Opportunities
Title: Assistant to the Regional Manager, Organization: IBM, Location: Scranton, PA, Pay: 10-15
Title: Associate Tattoo Artist, Organization: Tit 4 Tat, Location: Brooklyn, NY, Pay: 250-275
Title: Lead Chef, Organization: Chipotle, Location: Denver, CO, Pay: 10-15
Title: Lead Guitarist, Organization: Philharmonic, Location: Woodstock, NY, Pay: 100-200
Title: Manager of Fun, Organization: IBM, Location: Albany, NY, Pay: 30-40
Title: Stunt Double, Organization: Equity, Location: Los Angeles, CA, Pay: 15-25";

        $Reformatter = new CsvStringReformatter([
            'input_columns' => ['Title', 'Organization', 'City', 'State', 'Min', 'Max'],
            'header' => 'All Opportunities',
            'row_template' => 'Title: {Title}, Organization: {Organization}, Location: {City}, {State}, Pay: {Min}-{Max}',
            'sort_by' => 'Title'
        ]);

        $output = $Reformatter->reformat($input);

        $this->assertEquals(
            $Reformatter->reformat($input),
            $expected_output
        );
    }
}
