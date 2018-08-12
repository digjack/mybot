<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Service\BotService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class ContactController extends Controller
{
    //
    public function export(){
        $contacts = [['昵称','备注','签名','性别','省份','城市']];
        $friends = BotService::getFriends();
        foreach ($friends as $friend){
            $contacts []= [
                'NickName' => $friend['NickName'],
                'RemarkName' => $friend['RemarkName'],
                'Signature' => $friend['Signature'],
                'Sex' => $this->transferSex($friend['Sex']),
//                'AttrStatus' => $friend['AttrStatus'],
                'Province' => $friend['Province'],
                'City' => $friend['City']
            ];
        }
        $fileName = 'contacts_'.date('YmdHis').'.csv';
//        $contacts = $this->transferCode($contacts);
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($contacts);
        $writer = new Csv($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$fileName.'"');
        $fp = fopen('php://output', 'w');
        fwrite($fp,chr(0xEF).chr(0xBB).chr(0xBF));
        $writer->save("php://output");
    }

    protected function transferSex($sex){
        if($sex == 1){
            return '男';
        }
        if($sex == 2){
            return '女';
        }
        return '未知';
    }

    protected function transferCode($contacts){
        $res = [];
        foreach ($contacts as $lineIndex =>$contact){
            foreach ($contact as $columnIndex => $item){
                try{
                    $res[$lineIndex][$columnIndex] = iconv("UTF-8", "GBK", $item);
                }catch (\Exception $e){
                    $res[$lineIndex][$columnIndex] = $item;
                }
            }
        }
        return $res;
    }
}
