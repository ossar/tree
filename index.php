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
     1 => ['id' =>  1, 'name' => 'a', 'pid' =>  0 ],
     2 => ['id' =>  2, 'name' => 'b', 'pid' =>  1 ],
     3 => ['id' =>  3, 'name' => 'c', 'pid' =>  2 ],
     4 => ['id' =>  4, 'name' => 'd', 'pid' =>  1 ],
     5 => ['id' =>  5, 'name' => 'e', 'pid' =>  3 ],
     6 => ['id' =>  6, 'name' => 'f', 'pid' =>  5 ],
     7 => ['id' =>  7, 'name' => 'g', 'pid' =>  2 ],
     8 => ['id' =>  8, 'name' => 'h', 'pid' =>  2 ],
     9 => ['id' =>  9, 'name' => 'i', 'pid' => 13 ],
    10 => ['id' => 10, 'name' => 'j', 'pid' =>  8 ],
    11 => ['id' => 11, 'name' => 'k', 'pid' =>  6 ],
    12 => ['id' => 12, 'name' => 'l', 'pid' => 11 ],
    13 => ['id' => 13, 'name' => 'm', 'pid' => 14 ],
];

$pArr = [];
foreach ($data as $key => $val) {
    $pArr[$val['id']] = $val['pid'];
}

header('Content-type: text/plain; charset=utf-8');

$tr = new MyTree;
$tree = $tr->makeTree($pArr);
dispTree($tree, $data);

$arr = [
    'debian'    => ['name'=>'', 'pid'=>''],
    'ubuntu'    => ['name'=>'', 'pid'=>'debian'],
    'kubuntu'   => ['name'=>'', 'pid'=>'ubuntu'],
    'slackware' => ['name'=>'', 'pid'=>''],
    'redhat'    => ['name'=>'', 'pid'=>''],
    'fedora'    => ['name'=>'', 'pid'=>'redhat'],
    'centos'    => ['name'=>'', 'pid'=>'redhat'],
];
$pArr = [];
foreach ($arr as $key => $val) {
    $pArr[$key] = $val['pid'];
}
$tree = $tr->makeTree($pArr);
echo "\n\n";
echo "Linux\n";
dispTree($tree, $arr, 0, '  ');

function dispTree($tree, $data, $level=0, $offset='')
{
    $size = count($tree);
    $cnt = 0;
    foreach ($tree as $id => $subTree) {
        printf("%s|\n%s+--[%s]\n"
            , $offset
            , $offset
            , $id
            , $data[$id]['name']
        );
        $cnt++;
        if ($cnt == $size) {
            $newOffset = $offset.'    ';
        } else {
            $newOffset = $offset.'|   ';
        }
        dispTree($subTree, $data, $level+1, $newOffset);
    }
}
