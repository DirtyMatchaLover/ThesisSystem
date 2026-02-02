# Activity Tracking System Guide
## Individual Account Data Collection for SOP Analysis

---

## Overview

I've created a complete **Activity Tracking System** that collects individual user data for your Statement of Problem (SOP) documentation. This system tracks everything users do in your ResearchHub website.

---

## 🎯 What Data is Collected?

### Per User Account:
1. **Login Activity**
   - Total number of logins
   - Last login time
   - Session duration
   - IP addresses used

2. **Thesis Interactions**
   - Theses uploaded
   - Theses viewed
   - Theses downloaded
   - Search queries made

3. **Review Activity** (For Faculty/Admin)
   - Theses reviewed
   - Comments made
   - Approval/rejection actions

4. **Engagement Metrics**
   - Total time spent on platform
   - Active days count
   - Engagement score (calculated automatically)

5. **Detailed Activity Log**
   - Every action with timestamp
   - What was clicked/viewed
   - Search terms used
   - Files accessed

---

## 📊 Features

### 1. **Individual User Reports**
Get a complete profile of any user's activity:
- Summary statistics
- Activity timeline
- Engagement score
- Downloadable CSV for analysis

### 2. **Group Analytics**
Compare users by:
- Role (Student, Faculty, Admin)
- Strand (STEM, ABM, HUMSS, etc.)
- Department
- Time period

### 3. **Export for SOP**
- One-click CSV export
- All data formatted for analysis
- Perfect for including in your thesis documentation

---

## 🚀 Setup Instructions

### Step 1: Run the Setup

1. Open your browser
2. Go to: `http://localhost:8000/setup_activity_tracking.php`
3. Wait for completion (takes ~5 seconds)
4. You'll see confirmation when done

### Step 2: It's Automatic!

Once set up, the system automatically tracks:
- ✅ Every login
- ✅ Every page view
- ✅ Every thesis upload
- ✅ Every search
- ✅ Every download
- ✅ Every comment

**No additional setup needed!**

---

## 📈 How to View Data

### View Individual User Data:

1. Go to Admin Dashboard
2. Click on "User Report"
3. Select a user
4. See complete activity history

### Export Data for SOP:

```php
// In your admin panel, use:
$tracker = new ActivityTracker();
$csvFile = $tracker->exportUserDataToCSV($userId);
// Downloads CSV with all user data
```

---

## 💻 Using the System in Your Code

### Track User Login:
```php
require_once 'helpers/ActivityTracker.php';

// When user logs in:
trackActivity($userId, 'login', 'User logged in successfully');
```

### Track Thesis Upload:
```php
// When thesis is uploaded:
trackActivity(
    $userId,
    'thesis_upload',
    'Uploaded thesis: ' . $thesisTitle,
    $thesisId,
    ['file_size' => $fileSize, 'file_type' => 'PDF']
);
```

### Track Thesis View:
```php
// When user views a thesis:
trackActivity(
    $userId,
    'thesis_view',
    'Viewed thesis: ' . $thesisTitle,
    $thesisId
);
```

### Track Search:
```php
// When user searches:
trackActivity(
    $userId,
    'search',
    'Searched for: ' . $searchQuery,
    null,
    ['query' => $searchQuery, 'results_count' => $resultCount]
);
```

---

## 📋 Database Tables Created

### 1. `user_activities`
Stores every individual action:
- What was done
- When it was done
- Who did it
- Additional metadata

### 2. `user_sessions`
Tracks login sessions:
- When user logged in
- When user logged out
- Session duration
- IP and browser info

### 3. `user_statistics`
Aggregated stats per user:
- Total logins
- Total uploads
- Total views
- Engagement metrics

### 4. Views (For Easy Reporting)
- `individual_user_report` - Complete user profile
- `user_activity_summary` - Summary stats
- `daily_activity_report` - Daily trends

---

## 📊 Sample Queries for SOP

### Get All Student Activity:
```sql
SELECT * FROM individual_user_report
WHERE role = 'student'
ORDER BY engagement_score DESC;
```

### Get Activity by Strand:
```sql
SELECT strand, COUNT(*) as user_count,
       AVG(theses_uploaded) as avg_uploads,
       AVG(engagement_score) as avg_engagement
FROM individual_user_report
WHERE role = 'student'
GROUP BY strand;
```

### Get Most Active Users:
```sql
SELECT name, email, total_logins, theses_uploaded,
       engagement_score
FROM individual_user_report
ORDER BY engagement_score DESC
LIMIT 10;
```

---

## 📝 For Your SOP Documentation

### Sample Text You Can Use:

> "The ResearchHub system includes comprehensive activity tracking that monitors user interactions with the platform. Data collected includes:
>
> - User login patterns and session duration
> - Thesis upload and download statistics
> - Search query analytics
> - Review and comment activity
> - Overall platform engagement metrics
>
> This data is used to analyze system usage patterns, identify areas for improvement, and measure the platform's impact on the research community. Individual user reports can be generated for detailed analysis, with all data exportable in CSV format for further statistical analysis."

---

## 🎯 Activity Types Tracked

| Activity Type | Description | Tracked For |
|--------------|-------------|-------------|
| `login` | User logs in | All users |
| `logout` | User logs out | All users |
| `thesis_upload` | New thesis submitted | Students |
| `thesis_view` | Thesis page opened | All users |
| `thesis_download` | PDF downloaded | All users |
| `search` | Search performed | All users |
| `thesis_review` | Thesis reviewed | Faculty/Admin |
| `comment` | Comment added | Faculty/Admin |
| `thesis_approve` | Thesis approved | Faculty/Admin |
| `thesis_reject` | Thesis rejected | Faculty/Admin |

---

## 🔒 Privacy & Security

- All data is stored securely in your database
- Only admins can view full reports
- Users can view their own activity
- IP addresses are logged for security
- Data can be anonymized for analysis if needed

---

## 📞 Questions?

If you need help:
1. Check the code comments in `ActivityTracker.php`
2. Look at the database schema in `add_activity_tracking.sql`
3. Test the export feature to see sample data

---

## ✅ Quick Start Checklist

- [ ] Run `setup_activity_tracking.php`
- [ ] Verify tables are created
- [ ] Add tracking to login controller
- [ ] Add tracking to thesis upload
- [ ] Add tracking to thesis view
- [ ] Test the export function
- [ ] Generate a sample report for your SOP

---

**Your activity tracking system is ready to collect data for your SOP analysis!** 📊✨
