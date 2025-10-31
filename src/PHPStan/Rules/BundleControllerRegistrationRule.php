<?php

declare(strict_types=1);

namespace Tourze\RoutingAutoLoaderBundle\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * 检查 Bundle 中的控制器是否被正确注册到 AttributeControllerLoader
 *
 * @implements Rule<InClassNode>
 */
class BundleControllerRegistrationRule implements Rule
{
    private array $checkedBundles = [];

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $node->getClassReflection();

        // 检查是否是控制器
        if (!$this->isController($classReflection)) {
            return [];
        }

        // 检查是否在 Bundle 中（通过路径判断）
        $fileName = $scope->getFile();
        if (!$this->isInBundle($fileName)) {
            return [];
        }

        // 检查命名空间是否包含 Admin（Admin 控制器不需要注册）
        $namespace = $classReflection->getName();
        if (str_contains($namespace, 'Admin')) {
            return [];
        }

        // 获取 Bundle 名称
        $bundleName = $this->getBundleName($fileName);
        if (null === $bundleName) {
            return [];
        }

        // 获取 Bundle 根目录
        $bundleRoot = $this->getBundleRoot($fileName);
        if (null === $bundleRoot) {
            return [];
        }

        // 检查是否有 AttributeControllerLoader
        $loaderPath = $bundleRoot . '/src/Service/AttributeControllerLoader.php';
        if (!file_exists($loaderPath)) {
            return [
                RuleErrorBuilder::message(sprintf(
                    '控制器 %s 在 Bundle %s 中，但该 Bundle 没有 AttributeControllerLoader 服务。请创建 %s/Service/AttributeControllerLoader.php 来注册控制器。'
                    . '按照 packages/access-token-bundle/src/Service/AttributeControllerLoader.php 来做',
                    $classReflection->getName(),
                    $bundleName,
                    $bundleName
                ))
                    ->line($node->getOriginalNode()->getStartLine())
                    ->build(),
            ];
        }

        // 检查控制器是否被注册
        if (!$this->isControllerRegistered($loaderPath, $classReflection->getName())) {
            return [
                RuleErrorBuilder::message(sprintf(
                    '控制器 %s 必须在 %s/Service/AttributeControllerLoader::autoload() 方法中注册，使用 $collection->addCollection($this->controllerLoader->load(%s::class));',
                    $classReflection->getName(),
                    $bundleName,
                    $classReflection->getDisplayName()
                ))
                    ->line($node->getOriginalNode()->getStartLine())
                    ->build(),
            ];
        }

        return [];
    }

    private function isController(ClassReflection $classReflection): bool
    {
        // 排除抽象控制器 - 抽象控制器不需要注册路由
        if ($classReflection->isAbstract()) {
            return false;
        }

        // 检查是否继承 AbstractController
        if ($classReflection->isSubclassOf(AbstractController::class)) {
            return true;
        }

        // 检查是否有 Controller 后缀
        if (str_ends_with($classReflection->getName(), 'Controller')) {
            return true;
        }

        return false;
    }

    private function isInBundle(string $fileName): bool
    {
        // 检查路径是否包含 -bundle
        $isInBundle = str_contains($fileName, '-bundle/') || str_contains($fileName, '-bundle\\');

        // 排除测试文件
        if ($isInBundle && (str_contains($fileName, '/tests/') || str_contains($fileName, '\tests\\'))) {
            return false;
        }

        return $isInBundle;
    }

    private function getBundleName(string $fileName): ?string
    {
        // 从路径中提取 bundle 名称
        if (preg_match('#/([^/]+-bundle)/#', $fileName, $matches)) {
            return $matches[1];
        }
        if (preg_match('#\\\([^\\\]+-bundle)\\\#', $fileName, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function getBundleRoot(string $fileName): ?string
    {
        // 获取 bundle 根目录
        if (preg_match('#^(.+/[^/]+-bundle)/#', $fileName, $matches)) {
            return $matches[1];
        }
        if (preg_match('#^(.+\\\[^\\\]+-bundle)\\\#', $fileName, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function isControllerRegistered(string $loaderPath, string $controllerClass): bool
    {
        if (!file_exists($loaderPath)) {
            return false;
        }

        $content = file_get_contents($loaderPath);
        if (false === $content) {
            return false;
        }

        // 提取控制器的短名称
        $parts = explode('\\', $controllerClass);
        $shortName = end($parts);

        // 检查多种可能的注册格式
        $patterns = [
            // 完整类名：FullNamespace\Controller::class
            sprintf('/%s::class/', preg_quote($shortName, '/')),
            // 使用 use 语句的情况
            sprintf('/use\s+%s;/', preg_quote($controllerClass, '/')),
            // 字符串形式（虽然不推荐）
            sprintf('/[\'"]%s[\'"]/', preg_quote($controllerClass, '/')),
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                // 还要确保它在 addCollection 调用中
                if (str_contains($content, 'addCollection') && str_contains($content, 'controllerLoader->load')) {
                    return true;
                }
            }
        }

        return false;
    }
}
