<?php
namespace MyTree;

use Exception;

ini_set('display_errors', 'on');
error_reporting(E_ALL);

class MyTree
{
    public function makeTree($data)
    {
        // pathの作成
        $path = $this->makePath($data);
        // pathを間引く
        $prevKey = null;
        $prevVal = null;
        foreach ($path as $key => $val) {
            if ($prevKey !== null && strpos($val, $prevVal) === 0) {
                unset($path[$prevKey]);
            }
            $prevKey = $key;
            $prevVal = $val;
        }
        // ツリーの作成
        $tree = [];
        foreach ($path as $val) {
            $keyArr = explode(':', $val);
            $tree = $this->makeBranch($tree, $keyArr);
        }
        return $tree;
    }

    public function makeBranch($tree, $keyArr)
    {
        $key = array_shift($keyArr);
        if ($key === null) {
            return $tree;
        } else {
            $subTree = isset($tree[$key]) ? $tree[$key] : [];
            if ($keyArr) {
                $tree[$key] = $this->makeBranch($subTree, $keyArr);
            } else {
                $tree[$key] = $subTree;
            }
            return $tree;
        }
    }

    /**
     * パスの作成
     * @param  array
     * @return array
     */
    private function makePath($data)
    {
        $path = [];
        $left = array_keys($data);
        $cnt = 0;
        while ($left) {
            foreach ($left as $key => $id) {
                $pid = $data[$id];
                if (!isset($data[$pid])) {
                    $path[$id] = (string)$id;
                    unset($left[$key]);
                } elseif (isset($path[$pid])) {
                    $path[$id] = "{$path[$pid]}:{$id}";
                    unset($left[$key]);
                }
            }
            $cnt++;
            if ($cnt > 100) {
                throw new Exception();
            }
        }
        return $path;
    }

    public function dispTree($tree, $data, $option=[], $level=0, $offset='')
    {
        $option['pref_next'] = isset($option['pref_next']) ? $option['pref_next'] : ' |';
        $option['pref_none'] = isset($option['pref_none']) ? $option['pref_none'] : '  ';
        $option['pref_fold'] = isset($option['pref_fold']) ? $option['pref_fold'] : ' +';
        $option['default'  ] = isset($option['default'  ]) ? $option['default'  ] : '  ';
        $option['connect'  ] = isset($option['connect'  ]) ? $option['connect'  ] : '--';

        $size = count($tree);
        $cnt = 0;
        foreach ($tree as $id => $subTree) {
            if ($level == 0) {
                printf("\n[%s] %s\n"
                    , $id
                    , isset($data[$id]['comment']) ? $data[$id]['comment']: ''
                );
            } else {
                printf("%s%s\n%s%s%s[%s] %s\n"
                    , $offset
                    , $option['pref_next']
                    , $offset
                    , $option['pref_fold']
                    , $option['connect']
                    , $id
                    , isset($data[$id]['comment']) ? $data[$id]['comment']: ''
                );
            }
            $cnt++;
            if ($level == 0) {
                $newOffset = $offset;
            } else {
                if ($cnt == $size) {
                    $newOffset = $offset.$option['pref_none'].$option['default'];
                } else {
                    $newOffset = $offset.$option['pref_next'].$option['default'];
                }
            }
            $this->dispTree($subTree, $data, $option, $level+1, $newOffset);
        }
    }
}

$data = [
     1 => ['pid' =>  0 , 'comment' => 'a' ],
     2 => ['pid' =>  1 , 'comment' => 'b' ],
     3 => ['pid' =>  2 , 'comment' => 'c' ],
     4 => ['pid' =>  1 , 'comment' => 'd' ],
     5 => ['pid' =>  3 , 'comment' => 'e' ],
     6 => ['pid' =>  5 , 'comment' => 'f' ],
     7 => ['pid' =>  2 , 'comment' => 'g' ],
     8 => ['pid' =>  2 , 'comment' => 'h' ],
     9 => ['pid' => 13 , 'comment' => 'i' ],
    10 => ['pid' =>  8 , 'comment' => 'j' ],
    11 => ['pid' =>  6 , 'comment' => 'k' ],
    12 => ['pid' => 11 , 'comment' => 'l' ],
    13 => ['pid' => 14 , 'comment' => 'm' ],
];

$pArr = [];
foreach ($data as $key => $val) {
    $pArr[$key] = $val['pid'];
}

header('Content-type: text/plain; charset=utf-8');


$tr = new MyTree;
$tree = $tr->makeTree($pArr);
print_r($tree);
$tr->dispTree($tree, $data);

$arr = [
    'linux'     => ['pid'=>''      ],
    'debian'    => ['pid'=>'linux' ],
    'ubuntu'    => ['pid'=>'debian'],
    'kubuntu'   => ['pid'=>'ubuntu'],
    'slackware' => ['pid'=>'linux' ],
    'redhat'    => ['pid'=>'linux' ],
    'fedora'    => ['pid'=>'redhat'],
    'centos'    => ['pid'=>'redhat'],
];
$pArr = [];
foreach ($arr as $key => $val) {
    $pArr[$key] = $val['pid'];
}
$tree = $tr->makeTree($pArr);
echo "\n\n";

echo "Linux\n";
$option = [
    'pref_next' => '  |',
    'pref_none' => '   ',
    'pref_fold' => '  +',
    'default'   => '   ',
    'connect'   => null,
];
$tr->dispTree($tree, $arr, $option);

