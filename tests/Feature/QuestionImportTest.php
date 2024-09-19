<?php

use App\Imports\QuestionImport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelExcel;

it('it imports Questions from ExcelSheet', function () {

    $file = storage_path('8-sinf.xlsx');

    // dd($file);

    $this->assertFileExists($file);

    Excel::import(new QuestionImport(108,1), $file,readerType: ExcelExcel::XLSX);

    $this->assertTrue(true);

});
