# Symfony Routing Auto Loader Bundle 测试说明

本目录包含 Symfony Routing Auto Loader Bundle 的测试用例。

## 测试结构

测试分为两部分：
- **单元测试** (`Unit/`): 测试各个组件的独立功能
  - `Service/RoutingAutoLoaderEnhancerTest.php`: 测试路由自动加载器增强器的所有功能
- **集成测试** (`Integration/`): 测试组件的集成功能
  - `SimplifiedIntegrationTest.php`: 测试路由自动加载器的基本集成功能

## 运行测试

在项目根目录运行以下命令执行测试：

```bash
./vendor/bin/phpunit packages/symfony-routing-auto-loader-bundle/tests
```

## 测试覆盖范围

当前测试覆盖以下功能：

### 单元测试

- RoutingAutoLoaderEnhancer
  - load() 方法：
    - 无自动加载器时的行为
    - 单个自动加载器时的行为
    - 多个自动加载器时的行为
    - 空自动加载器集合的行为
    - 路由名称冲突时的行为
    - 自动加载器抛出异常时的行为
    - 非接口对象的处理
  - supports() 方法代理行为
  - getResolver() 方法代理行为
  - setResolver() 方法代理行为

### 集成测试

- 路由集合合并功能
- 路由名称冲突处理
- 接口自动配置标签功能

## 注意事项

- 集成测试使用了简化的方法，不依赖完整的 Symfony 内核，以避免复杂的依赖和配置问题
- 测试用例设计遵循边界、异常和正常流程覆盖原则 