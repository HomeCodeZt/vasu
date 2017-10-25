<?php

namespace AppBundle\DependencyInjection\ExportService;

use AppBundle\Entity\Visitor;


/**
 * Class ExportService
 * @package AppBundle\DependencyInjection\ExportService
 */
class ExportService
{
    /**
     * @param array $result
     */
    public function createCsv(array $result)
    {
        $file = 'export/report.csv';
        $file = new \SplFileObject($file, 'w+');
        $paramsTitle = ['Учасники судового процесу Вищого адміністративного суду України'];
        $paramsEmpty = [
            '-----------------------',
            '-----------------------',
            '-----------------------',
            '-----------------------',
            '-----------------------',
            '-----------------------',
            '-----------------------',
            '-----------------------',
            '-----------------------',
            '-----------------------',
        ];
        $paramsHeader = [
            'Номер справи',
            'Імя',
            'Прізвище',
            'По батькові',
            'Учасник',
            'Документ',
            'Номер документа',
            'Дата видачі і назва органу',
            'Примітка',
            'Дата',
        ];

        $file->fputcsv($this->convertorUtf8toWin1251($paramsTitle), ";");
        $file->fputcsv($this->convertorUtf8toWin1251($paramsEmpty), ";");
        $file->fputcsv($this->convertorUtf8toWin1251($paramsHeader), ";");

        foreach ($result as $row) {
            /** @var Visitor $visitor */
            $visitor = $row['visitor'];
            $fileNumber = $row['fileNumber'];
            $typeName = $row['typeName'];
            $docType = $row['docType'];

            $params = [
                $fileNumber,
                $visitor->getFName(),
                $visitor->getSName(),
                $visitor->getTName(),
                $typeName,
                $docType,
                $visitor->getDocNum(),
                $visitor->getDocDescription(),
                $visitor->getNote(),
                $visitor->getDateVisit()->format('Y-m-d H:i:s'),
            ];
            $file->fputcsv($this->convertorUtf8toWin1251($params), ";");
        }
    }

    /**
     * @param array $params
     * @return array
     */
    private function convertorUtf8toWin1251(array $params)
    {
        foreach ($params as $key => $string) {
            $params[$key] = mb_convert_encoding($string, "windows-1251", "utf-8");
        }
        return $params;
    }

}