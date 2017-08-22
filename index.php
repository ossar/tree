<?php

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
dispTree($tree, $data);

$arr = [
    'debian'    => ['pid'=>''      ],
    'ubuntu'    => ['pid'=>'debian'],
    'kubuntu'   => ['pid'=>'ubuntu'],
    'slackware' => ['pid'=>''      ],
    'redhat'    => ['pid'=>''      ],
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
dispTree($tree, $arr, '', '  |', '   ', '  +', '   ');


function dispTree($tree, $data, $offset='', $prefNext=' |', $prefNone='  ', $prefFold=' +', $defaultOffset='  ', $connect='--')
{
    $size = count($tree);
    $cnt = 0;
    foreach ($tree as $id => $subTree) {
        printf("%s{$prefNext}\n%s{$prefFold}{$connect}[%s] %s\n"
            , $offset
            , $offset
            , $id
            , isset($data[$id]['comment']) ? $data[$id]['comment']: ''
        );
        $cnt++;
        if ($cnt == $size) {
            $newOffset = $offset.$prefNone.$defaultOffset;
        } else {
            $newOffset = $offset.$prefNext.$defaultOffset;
        }
        dispTree($subTree, $data, $newOffset, $prefNext, $prefNone, $prefFold, $defaultOffset, $connect);
    }
}
