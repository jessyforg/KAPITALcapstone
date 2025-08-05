# Contributing to KAPITAL

Thank you for your interest in contributing to KAPITAL! This guide will help you get started with contributing to our startup ecosystem platform.

## 🌟 How to Contribute

We welcome contributions from developers, designers, documentation writers, and startup ecosystem enthusiasts. Here are ways you can help:

### Types of Contributions

- **🐛 Bug Reports**: Help us identify and fix issues
- **💡 Feature Requests**: Suggest new features or improvements
- **📝 Documentation**: Improve guides, tutorials, and code documentation
- **💻 Code Contributions**: Fix bugs, implement features, optimize performance
- **🎨 Design Improvements**: Enhance UI/UX and visual design
- **🧪 Testing**: Help test new features and find edge cases

## 🚀 Getting Started

### 1. Fork and Clone

```bash
# Fork the repository on GitHub
# Then clone your fork
git clone https://github.com/YOUR_USERNAME/KAPITALcapstone.git
cd KAPITALcapstone
```

### 2. Set Up Development Environment

```bash
# Navigate to the main application directory
cd "public_html (1)"

# Install dependencies
composer install

# Set up your local database
# Follow the INSTALLATION.md guide for detailed setup
```

### 3. Create a Branch

```bash
# Create a new branch for your feature/fix
git checkout -b feature/your-feature-name
# or
git checkout -b fix/bug-description
```

## 📋 Development Guidelines

### Code Style

#### PHP Code Standards
We follow PSR-12 coding standards:

```php
<?php

declare(strict_types=1);

namespace Kapital\Services;

/**
 * User service for managing user operations
 */
class UserService
{
    private DatabaseConnection $conn;
    
    public function __construct(DatabaseConnection $connection)
    {
        $this->conn = $connection;
    }
    
    /**
     * Retrieve user by ID
     *
     * @param int $userId User identifier
     * @return array|null User data or null if not found
     */
    public function getUserById(int $userId): ?array
    {
        // Implementation here
    }
}
```

#### HTML/CSS Standards
```html
<!-- Use semantic HTML5 elements -->
<article class="startup-profile">
    <header class="startup-profile__header">
        <h1 class="startup-profile__title">Startup Name</h1>
    </header>
    <section class="startup-profile__content">
        <!-- Content here -->
    </section>
</article>
```

```css
/* Use BEM methodology for CSS classes */
.startup-profile {
    margin: 1rem 0;
}

.startup-profile__header {
    padding: 1rem;
    background-color: var(--primary-color);
}

.startup-profile__title {
    font-size: 1.5rem;
    color: var(--text-primary);
}
```

#### JavaScript Standards
```javascript
// Use modern ES6+ features
const ApiService = {
    async fetchUserData(userId) {
        try {
            const response = await fetch(`/api/users/${userId}`);
            return await response.json();
        } catch (error) {
            console.error('Failed to fetch user data:', error);
            throw error;
        }
    }
};

// Use descriptive variable names
const isUserVerified = user.verification_status === 'verified';
const hasStartupProfile = user.startup_profile_id !== null;
```

### Database Guidelines

#### Naming Conventions
```sql
-- Table names: plural, snake_case
CREATE TABLE startup_profiles (
    startup_profile_id INT PRIMARY KEY,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Column names: snake_case
-- Use descriptive names
funding_stage VARCHAR(50)
verification_status ENUM('pending', 'verified', 'rejected')
```

#### SQL Best Practices
```php
// Always use prepared statements
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND verification_status = ?");
$stmt->bind_param("ss", $email, $status);
$stmt->execute();

// Handle errors gracefully
if (!$stmt) {
    error_log("Database prepare failed: " . $conn->error);
    throw new DatabaseException("Query preparation failed");
}
```

## 🔧 Development Workflow

### 1. Issue Selection

#### Finding Issues to Work On
- Check the [Issues](https://github.com/jessyforg/KAPITALcapstone/issues) page
- Look for labels like `good first issue`, `help wanted`, or `bug`
- Comment on the issue to express interest before starting work

#### Issue Labels
- **🐛 bug**: Something isn't working correctly
- **✨ enhancement**: New feature or improvement
- **📝 documentation**: Documentation improvements
- **🏷️ good first issue**: Good for newcomers
- **🆘 help wanted**: Extra attention needed
- **🔥 priority-high**: Urgent issues

### 2. Making Changes

#### Before You Code
1. **Understand the codebase**: Read through relevant files
2. **Check existing patterns**: Follow established conventions
3. **Plan your approach**: Consider edge cases and implications
4. **Test locally**: Ensure your development environment works

#### While Coding
1. **Write clear commit messages**:
   ```bash
   git commit -m "Add user profile validation for startup entrepreneurs"
   
   # More detailed commits when needed
   git commit -m "Fix AI token limit calculation
   
   - Correct daily token reset logic
   - Add proper error handling for token overflow
   - Update token usage display in UI
   
   Resolves #123"
   ```

2. **Keep commits focused**: One logical change per commit
3. **Test your changes**: Verify functionality works as expected
4. **Update documentation**: Add/update relevant documentation

### 3. Testing Your Changes

#### Manual Testing Checklist
- [ ] Feature works as intended
- [ ] No new PHP errors or warnings
- [ ] Database operations function correctly
- [ ] File uploads work (if applicable)
- [ ] UI displays properly on different screen sizes
- [ ] AI features respond correctly (if modified)

#### Test Different User Roles
- [ ] Entrepreneur functionality
- [ ] Investor functionality  
- [ ] Job seeker functionality
- [ ] Admin panel (if applicable)

#### Cross-Browser Testing
- [ ] Chrome/Chromium
- [ ] Firefox
- [ ] Safari (if available)
- [ ] Edge

### 4. Pull Request Process

#### Before Submitting
1. **Update your branch**:
   ```bash
   git fetch origin
   git rebase origin/main
   ```

2. **Run final tests**: Ensure everything still works
3. **Review your changes**: Check diff for unintended modifications

#### Pull Request Template
When creating a pull request, include:

```markdown
## Description
Brief description of what this PR does.

## Type of Change
- [ ] Bug fix (non-breaking change which fixes an issue)
- [ ] New feature (non-breaking change which adds functionality)  
- [ ] Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] Documentation update

## Testing
Describe how you tested your changes:
- [ ] Manual testing performed
- [ ] Tested with different user roles
- [ ] Cross-browser testing completed

## Screenshots (if applicable)
Add screenshots to show visual changes.

## Checklist
- [ ] My code follows the project's coding standards
- [ ] I have performed a self-review of my code
- [ ] I have commented my code where necessary
- [ ] I have updated documentation where needed
- [ ] My changes generate no new warnings or errors

## Related Issues
Closes #123
```

## 🐛 Reporting Bugs

### Before Reporting
1. **Search existing issues**: Check if the bug has already been reported
2. **Test on latest version**: Ensure you're using the current codebase
3. **Isolate the problem**: Try to reproduce with minimal steps

### Bug Report Template
```markdown
## Bug Description
A clear and concise description of what the bug is.

## To Reproduce
Steps to reproduce the behavior:
1. Go to '...'
2. Click on '....'
3. Scroll down to '....'
4. See error

## Expected Behavior
What you expected to happen.

## Actual Behavior
What actually happened.

## Screenshots
If applicable, add screenshots to help explain your problem.

## Environment
- **OS**: [e.g. Windows 10, macOS 11.0, Ubuntu 20.04]
- **Browser**: [e.g. Chrome 96, Firefox 95, Safari 15]
- **PHP Version**: [e.g. 7.4.3]
- **Database**: [e.g. MySQL 8.0.25]

## Error Messages
```
Paste any error messages from logs or browser console
```

## Additional Context
Add any other context about the problem here.
```

## 💡 Suggesting Features

### Feature Request Template
```markdown
## Feature Summary
Brief summary of the feature request.

## Problem Statement
What problem does this feature solve? Who would benefit?

## Proposed Solution
Detailed description of the proposed feature.

## Alternative Solutions
Other approaches you've considered.

## Implementation Ideas
Technical suggestions for implementation (optional).

## Additional Context
Any other relevant information, mockups, or examples.
```

## 📝 Documentation Contributions

### Documentation Standards
- **Clear and concise**: Write for users of all technical levels
- **Step-by-step**: Provide detailed instructions with examples
- **Screenshots**: Include visual aids where helpful
- **Up-to-date**: Ensure accuracy with current codebase

### Documentation Areas
- **User guides**: Help users understand platform features
- **Developer docs**: Technical documentation for contributors
- **API documentation**: Endpoint specifications and examples
- **Setup guides**: Installation and configuration instructions

## 🔒 Security Considerations

### Reporting Security Issues
**DO NOT** create public issues for security vulnerabilities. Instead:
1. Email security concerns to the development team
2. Include detailed description of the vulnerability
3. Provide steps to reproduce (if safe to do so)
4. Allow reasonable time for fix before public disclosure

### Security Best Practices
- **Input validation**: Always validate and sanitize user input
- **SQL injection prevention**: Use prepared statements
- **XSS protection**: Escape output data
- **Authentication**: Implement proper session management
- **File uploads**: Validate file types and sizes
- **Error handling**: Don't expose sensitive information in errors

## 🎯 Project Priorities

### Current Focus Areas
1. **User Experience**: Improving platform usability and accessibility
2. **AI Features**: Enhancing AI advisor capabilities and performance
3. **Mobile Responsiveness**: Ensuring great mobile experience
4. **Performance**: Optimizing load times and database queries
5. **Security**: Strengthening security measures and best practices

### Future Roadmap
- Mobile app development
- Advanced analytics dashboard  
- Multi-language support
- Integration with external services
- Enhanced matchmaking algorithms

## 🤝 Code Review Process

### For Contributors
- **Be responsive**: Address feedback promptly and professionally
- **Ask questions**: Seek clarification when feedback is unclear
- **Learn from feedback**: Use reviews as learning opportunities
- **Stay professional**: Maintain respectful communication

### For Reviewers
- **Be constructive**: Provide helpful, actionable feedback
- **Explain reasoning**: Help contributors understand suggestions
- **Recognize good work**: Acknowledge well-written code
- **Be timely**: Review pull requests in reasonable timeframe

## 🆘 Getting Help

### Resources
- **Documentation**: Check all .md files in the repository
- **Issues**: Search existing issues for solutions
- **Discussions**: Use GitHub Discussions for questions
- **Code Examples**: Look at existing code for patterns

### Communication Channels
- **GitHub Issues**: Bug reports and feature requests
- **GitHub Discussions**: General questions and ideas
- **Pull Request Comments**: Code-specific discussions
- **Email**: Security issues and private concerns

## 🏆 Recognition

We appreciate all contributions! Contributors will be:
- **Credited**: Listed in project contributors
- **Recognized**: Mentioned in release notes for significant contributions
- **Supported**: Helped to grow their skills and experience

## 📄 License

By contributing to KAPITAL, you agree that your contributions will be licensed under the same license as the project.

---

## Thank You! 🙏

Your contributions help build a stronger startup ecosystem in the Cordillera region. Every bug fix, feature addition, and documentation improvement makes a difference for entrepreneurs, investors, and job seekers using our platform.

**Let's build something amazing together!** 