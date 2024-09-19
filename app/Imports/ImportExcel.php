<?php

namespace App\Imports;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelExcel;


class ImportExcel
{
    public static function run(object $object, string|UploadedFile $file): ExcelExcel
    {
        return Excel::import($object, $file, readerType: ExcelExcel::XLSX);
    }

}
