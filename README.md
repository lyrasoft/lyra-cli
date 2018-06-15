# LYRASOFT CLI 工具

這是為了 LYRASOFT 日常工作與建立設定檔所開發的方便工具。

## 安裝方式

請執行

```bash
composer global require lyrasoft/cli
```

安裝完成後，可在任何地方直接輸入:

```bash
lyra <command>
```

來執行相關指令。

### Mac 無法執行時

Mac 必須先將 ~/.composer/vendor/bin 加入到 PATH 環境變數

請參考 https://stackoverflow.com/a/25373254

```bash
vim ~/.bash_profile
export PATH=$HOME/.composer/vendor/bin:$PATH
source ~/.bash_profile
```

## 常用功能說明

### 更新 PhpStorm 設定檔

我們的常用設定檔有三組，分別是 CodeStyle, FileTemplate 與 LiveTemplate

可以用：

```bash
lyra pstorm pull-config -a -g
```

來更新 PhpStorm 的全域設定檔。也可以用：

```bash
lyra pstorm pull-config -a
```

單純只更新到現在的專案

如果你要把自己設定好的設定檔更新到我們得 repository 給其他人分享，可以用：

```bash
lyra pstorm push-config -a
```

**注意這個動作不要隨便進行**

### Get PR

現在可以透過 lyra cli 快速取用 PR 來測試，取代以前的 `get-pr` 指令

```bash
lyra pr {number}
```

預設會拉下來成 `pr-xxx` 的 branch，也可以指定特定的 branch：

```bash
lyra pr {number} branch_name
```

拉下後直接切換

```bash
lyra pr {number} -c
```

預設是從 `lyra` 的 remote 取用 PR，請按照命名原則設定你的 git remote，也可以自定 remote

```bash
lyra pr {number} -r=upstream
```

### PHP Code Sniffer

先切換到專案目錄(確定已經先用 phpstorm 開啟過這個目錄，有 .idea 資料夾)，第一次先執行：

```bash
lyra pstorm sniffer -p
```

就會自動設定好我們的 Sniffer 設定檔，並下載我們的 Code Style 規則。第二次之後可以省略 `-p` 直接啟用 phpstorm 的 sniffer 功能。

```bash
lyra pstorm sniffer
```

如果未來我們的 Sniffer 規則有更動，只要再執行第一個指令或者再任何地方執行：

```bash
lyra sniffer sync
```

就會自動刷新 Sniffer 規則

## 完整指令說明

請直接用 `-h` 觀看說明

## 開發方式

若要增加或修改功能，最快的方法是先安裝完 lyra-cli，接著 cd 到 Composer 的 global 目錄:

- Windows: `C:\Users\<user_name>\AppData\Roaming\Composer`
- Mac: `~/.composer`

lyra-cli 的目錄在 `vendor/lyrasoft/cli` 內，將 `cli` 目錄移除，直接 git clone lyra-cli 的專案過來取代，就能一邊開發一邊測試了，
開發完可以直接 git push。 (別忘了先 fork)

### Command 的編寫方式

最上層 Command 請寫好 class 之後，註冊在 `/lyra` 檔案內。

See: https://github.com/lyrasoft/lyra-cli/blob/6c76cacd4a62393337e6c37f49f80dd263bd920d/lyra#L34

之後的寫法請參考: [Windwalker Console](https://github.com/ventoviro/windwalker-console#windwalker-console)
