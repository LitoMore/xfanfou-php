<?php

namespace App\Http\Controllers\Home\M;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Library\Api;

class HomeController extends BaseController
{
    public function getIndex()
    {
        return view('m.index');
    }

    public function getHome($page = 1)
    {
        $home_timeline = Api\Statuses::homeTimeline([
            'count' => 15,
            'page' => $page,
            'format' => 'html'
        ]);
        $this->msg = \Session::get('msg');

        if ($home_timeline['code'] != 0) {
            $this->msg = $home_timeline['error'];
        }

        // 存储每条消息需要@的人
        $stat = [];
        foreach ($home_timeline['content'] as $status) {
            $stat[$status->id] = [
                'text' => getStatusText($status->text),
                'ats' => getAts('@' . $status->user->name . ' ' . $status->text),
                'name' => $status->user->name
            ];
        }
        \Session::set('stat', $stat);

        return view('m.home')->with([
            'title' => '首页',
            'page' => $page,
            'homeTimeline' => $home_timeline['content'],
            'msg' => $this->msg,
            'notification' => $this->getNotification()
        ]);
    }

    public function postHome(Request $request)
    {
        $update = Api\Statuses::update($request->all());

        $this->msg = '发送成功';

        if ($update['code'] != 0) {
            $this->msg = $update['error'];
        }

        // 是一条转发或回复消息则存一条flash
        if ($request->has('in_reply_to_status_id') || $request->has('repost_status_id')) {
            \Session::flash('replied', true);
        }

        \Session::flash('msg', $this->msg);

        return back();
    }
}
