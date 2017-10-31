<?php

namespace AppBundle\DependencyInjection\ExportService;

use AppBundle\Entity\EventsLog;
use AppBundle\Entity\Visitor;


/**
 * Class ExportService
 * @package AppBundle\DependencyInjection\ExportService
 */
class ExportService
{

    const FLAG_SEARCH = 'search';
    const FLAG_LOG = 'log';

    /**@desc Шаблон для поиска
     * @var array
     */
    private $paramsTitleSearch = ['Учасники судового процесу Вищого адміністративного суду України'];
    private $paramsEmptySearch = [
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
    private $paramsHeaderSearch = [
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

    /**
     * @desc Шаблон для логера
     * @var array
     */
    private $paramsTitleLog = ['Список дiй вciх операторiв'];
    private $paramsEmptyLog = [
        '-----------------------',
        '-----------------------',
        '-----------------------',
        '-----------------------',
        '-----------------------',
        '-----------------------',
        '-----------------------',
        '-----------------------',
    ];
    private $paramsHeaderLog= [
        'Номер учасника процесу',
        'Номер справи',
        'Прізвище',
        'Ім\'я',
        'По батькові',
        'Оператор',
        'Дiя',
        'Дата',
    ];


    /**
     * @param array $result
     */
    public function createCsv(array $result, $flag)
    {
        $file = 'export/report.csv';
        $file = new \SplFileObject($file, 'w+');

        $paramsTitle =  null;
        $paramsEmpty =  null;
        $paramsHeader = null;
        
            switch ($flag){
               case self::FLAG_SEARCH;
                       $paramsTitle = $this->paramsTitleSearch;
                       $paramsEmpty = $this->paramsEmptySearch;
                       $paramsHeader =$this->paramsHeaderSearch;
                   break;
                case self::FLAG_LOG;
                        $paramsTitle = $this->paramsTitleLog;
                        $paramsEmpty = $this->paramsEmptyLog;
                        $paramsHeader =$this->paramsHeaderLog;
                   break;
            }


        $file->fputcsv($this->convertorUtf8toWin1251($paramsTitle), ";");
        $file->fputcsv($this->convertorUtf8toWin1251($paramsEmpty), ";");
        $file->fputcsv($this->convertorUtf8toWin1251($paramsHeader), ";");

        switch ($flag){
            case self::FLAG_SEARCH;
                $this->createSearchLog($result,$file);
                break;
            case self::FLAG_LOG;
                $this->createEventsLog($result,$file);
                break;
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

    /**
     * @param $result
     * @param \SplFileObject $file
     */
    private function createSearchLog($result,$file){
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
     * @param $result
     * @param \SplFileObject $file
     */
    private function createEventsLog($result,$file){
        foreach ($result as $row) {
            /** @var EventsLog $log */
            $log = $row['log'];

            $params = [
                $row['number'],
                $row['fileNumber'],
                $row['fName'],
                $row['sName'],
                $row['tName'],
                $row['login'],
                $log->getEventType(),
                $log->getDate()->format('Y-m-d H:i:s'),
            ];
            $file->fputcsv($this->convertorUtf8toWin1251($params), ";");
        }
    }

}