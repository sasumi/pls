# Plite 类型项目脚手架工具
用于创建使用 Plite 框架的 MVC项目

## 使用方法

```shell
# 创建项目，自动使用目录名称作为项目名称和命名空间
# 例如：创建项目名称为 MyProject，命名空间为 App\MyProject
composer create-project lfphp/pls MyProject

# 手动重置项目名称和命名空间
php pls.php reset

# 显示帮助信息
php pls.php help
```

## 功能说明

### 自动项目初始化
当使用 `composer create-project` 创建项目时，脚手架会自动：
1. 检测项目目录名称（例如：MyProject）
2. 自动设置项目名称为 `app/项目名称`（例如：app/myproject）
3. 自动生成命名空间（例如：App\Myproject）
4. 更新 composer.json 中的项目配置

### 手动配置
如果需要自定义项目名称，可以运行：
```shell
php pls.php reset
```
然后按提示输入自定义的项目名称。
