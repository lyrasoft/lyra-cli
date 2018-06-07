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

## 可用指令

請直接用 `-h` 觀看說明

## 開發方式

若要增加或修改功能，最快的方法是先安裝完 lyra-cli，接著 cd 到 Composer 的 global 目錄:

- Windows: `C:\Users\<user_name>\AppData\Roaming\Composer`
- Mac: `~/.composer`

lyra-cli 的目錄在 `vendor/lyrasoft/cli` 內，將 `cli` 目錄移除，直接 git clone lyra-cli 的專案過來取代，就能一邊開發一邊測試了，
開發完可以直接 git push。 (別忘了先 fork)

### Command 的編寫方市

最上層 Command 請寫好 class 之後，註冊在 `/lyra` 檔案內。

See: https://github.com/lyrasoft/lyra-cli/blob/65025fb2f8946d24bc317df2001b792fa3040bdd/lyra#L33

之後的寫法請參考: [Windwalker Console](https://github.com/ventoviro/windwalker-console#windwalker-console)
