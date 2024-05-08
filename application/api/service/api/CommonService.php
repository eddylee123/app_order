<?php
namespace app\api\service\api;


use app\api\library\Oss;
use app\api\service\BaseService;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class CommonService extends BaseService
{
    public function putExcel(array $header, array $data, string $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        //设置表头
        $col = 'A';
        foreach ($header as $title) {
            $sheet->setCellValue($col.'1', $title);
            $col++;
        }

        //设置内容
        $row = 2; // 从第二行开始填充数据
        foreach ($data as $rowData) {
            $index = 1;
            foreach ($rowData as $value) {
                is_null($value) && $value = '';
                $sheet->setCellValueExplicitByColumnAndRow($index, $row, $value, DataType::TYPE_STRING);
                $index++;
            }
            $row++;
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filePath = ROOT_PATH.'public'.DS.'uploads'.DS.$filename.date('ymdhis').'.xlsx';
        $writer->save($filePath);

        //上传到oss
        return Oss::upload($filename.'.xlsx', $filePath);
    }

    public function getObject(string $file)
    {
        $respUrl = Oss::getObject($file);
        if (empty($respUrl)) {
            app_exception('上传文件异常');
        }

        //保存文件
        $filePath = ROOT_PATH.'public'.DS.'uploads'.DS.$file.'.xlsx';

        $content = file_get_contents($respUrl);

        file_put_contents($filePath, $content);

        return $filePath;
    }

}