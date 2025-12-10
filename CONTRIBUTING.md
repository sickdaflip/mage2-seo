# Contributing to FlipDev SEO Extension

Thank you for considering contributing! 🎉

## 🐛 Reporting Bugs

Create an issue with:
- Clear title and description
- Steps to reproduce
- Expected vs actual behavior
- Magento & PHP versions
- Any relevant logs

## 💡 Suggesting Features

Create an issue describing:
- The feature
- Use cases
- Why it would be beneficial

## 🔧 Pull Requests

1. **Fork & Clone**
   ```bash
   git clone https://github.com/YOUR-USERNAME/mage2-seo.git
   cd mage2-seo
   ```

2. **Create Branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Make Changes**
   - Follow Magento 2 coding standards
   - Use strict types (`declare(strict_types=1)`)
   - Add comprehensive error handling
   - Include proper logging
   - Update documentation

4. **Test Your Changes**
   - Install in test Magento environment
   - Verify all functionality works
   - Check admin configuration
   - Test frontend output

5. **Commit**
   ```bash
   git add .
   git commit -m "feat: add amazing feature"
   ```
   
   Use conventional commits:
   - `feat:` - New feature
   - `fix:` - Bug fix
   - `docs:` - Documentation only
   - `style:` - Code style changes
   - `refactor:` - Code refactoring
   - `chore:` - Maintenance

6. **Push & Create PR**
   ```bash
   git push origin feature/your-feature-name
   ```

## 📝 Coding Standards

### PHP Requirements
- **PHP Version**: 8.4+ (minimum)
- **Strict Types**: Always use `declare(strict_types=1);`
- **Type Hints**: Use type hints for all parameters and return values
- **Null-Safe**: Use null-safe operators where appropriate
- **Error Handling**: Wrap operations in try-catch
- **Logging**: Use PSR-3 logger for errors and important events

### Code Style
- Follow [Magento 2 Coding Standards](https://developer.adobe.com/commerce/php/coding-standards/)
- Use meaningful variable and method names
- Keep methods focused and small
- Document complex logic

### Example

```php
<?php
/**
 * FlipDev SEO Extension
 * 
 * @category   FlipDev
 * @package    FlipDev_Seo
 * @author     Your Name <your.email@example.com>
 * @copyright  Copyright (c) 2025 FlipDev
 * @license    MIT
 */

declare(strict_types=1);

namespace FlipDev\Seo\Model;

use Psr\Log\LoggerInterface;

/**
 * Example Model Class
 */
class Example
{
    private LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Process input with error handling
     *
     * @param string $input
     * @return string|null
     */
    public function process(string $input): ?string
    {
        try {
            // Your logic here
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('FlipDev_Seo: Processing failed', [
                'exception' => $e->getMessage(),
                'input' => $input
            ]);
            return null;
        }
    }
}
```

## ✅ Quality Checklist

Before submitting, ensure:

- [ ] Code follows Magento 2 standards
- [ ] PHP 8.4+ features used appropriately
- [ ] All parameters and returns have type hints
- [ ] Strict types declared in all PHP files
- [ ] Comprehensive error handling added
- [ ] PSR-3 logging for errors
- [ ] Documentation is updated
- [ ] Tested in Magento environment
- [ ] No debugging code left behind
- [ ] Commit messages are clear

## 🔍 Review Process

1. Maintainer reviews PR
2. Address feedback if needed
3. Once approved, PR is merged
4. Your contribution is live! 🎉

## 💬 Questions?

Feel free to:
- Open an issue for questions
- Email: philippbreitsprecher@gmail.com

Thank you for contributing! 🙏
