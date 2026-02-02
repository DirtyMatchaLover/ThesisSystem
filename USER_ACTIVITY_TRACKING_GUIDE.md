# 📊 User Activity Tracking System - Complete Guide

## Overview

The system now tracks **individual user data** and provides **combined reports** for your Statement of Problem (SOP) analysis.

**Tracked Users:** Librarians, Faculty, and Students (Admin is excluded)

---

## 🎯 Two Types of Reports

### 1. **Individual User Reports**
Shows activity data for **ONE specific user**

### 2. **Combined Report (All Users)**
Shows aggregated data for **ALL tracked users** together (for SOP analysis)

---

## 📋 What Data is Tracked?

### For Each Individual User:
- ✅ Total activities performed
- ✅ Total logins
- ✅ Average session duration
- ✅ Thesis uploads
- ✅ Thesis views
- ✅ Thesis downloads
- ✅ Search queries
- ✅ Most active day
- ✅ Complete activity timeline

### For All Users Combined:
- ✅ Total number of users (by role)
- ✅ Total activities across all users
- ✅ Average activities per user
- ✅ Total logins (all users)
- ✅ Average session duration (all users)
- ✅ Activity breakdown by role (librarian vs faculty vs student)
- ✅ Most common activities
- ✅ Time-based trends (last 30 days)
- ✅ System usage score

---

## 🚀 How to Access Reports

### Individual User Report:

**Method 1: From Users Page**
1. Login as **Admin, Faculty, or Librarian**
2. Go to: **Dashboard** → **Manage Users**
3. Find the user you want to view
4. Click **"📊 View Activity Report"** button
5. View their individual activity data

**Method 2: Direct URL**
```
http://localhost:8000/index.php?route=admin/individual-report&user_id=USER_ID
```

---

### Combined Report (All Users - For SOP):

**Method 1: From Navigation Menu**
1. Login as **Admin, Faculty, or Librarian**
2. Click your name (top-right corner)
3. Click **"📊 All Users Activity (SOP Data)"**
4. View combined report for all users

**Method 2: Direct URL**
```
http://localhost:8000/index.php?route=admin/combined-report
```

**Live Website:**
```
https://nonenlightened-diligently-sabra.ngrok-free.dev/index.php?route=admin/combined-report
```

---

## 📥 Exporting Data for SOP Analysis

### Export Individual User Data:
1. Go to the individual user report page
2. Click **"📥 Export to CSV"**
3. Save the CSV file
4. Open in Excel, Google Sheets, or SPSS

### Export Combined Data (Recommended for SOP):
1. Go to the combined report page
2. Click **"📥 Export All User Data to CSV (For SOP Analysis)"**
3. Save the CSV file
4. This file contains ALL users' activity data
5. Use for statistical analysis, charts, and answering research questions

---

## 📊 CSV Export Format

### Individual User CSV:
```csv
User Name, Email, Role, Activity Type, Description, Thesis, Date/Time
John Doe, john@pcc.edu.ph, student, thesis_upload, Uploaded thesis, My Thesis Title, 2026-02-02 10:30:00
John Doe, john@pcc.edu.ph, student, thesis_view, Viewed thesis, Another Thesis, 2026-02-02 11:15:00
```

### Combined CSV (All Users):
```csv
User Name, Email, Role, Activity Type, Description, Thesis, Date/Time
John Doe, john@pcc.edu.ph, student, thesis_upload, Uploaded thesis, Thesis A, 2026-02-02 10:30:00
Jane Smith, jane@pcc.edu.ph, faculty, thesis_view, Viewed thesis, Thesis B, 2026-02-02 11:00:00
Bob Jones, bob@pcc.edu.ph, librarian, thesis_download, Downloaded thesis, Thesis C, 2026-02-02 12:00:00
```

---

## 🎓 How to Use This Data for Your SOP

### Research Questions You Can Answer:

1. **User Engagement:**
   - How many users actively use the system?
   - What is the average number of activities per user?
   - Which role (librarian, faculty, student) is most active?

2. **System Usage:**
   - What are the most common user actions?
   - How long do users spend in the system (session duration)?
   - What is the system usage score?

3. **Thesis Interactions:**
   - How many theses are uploaded?
   - How many times are theses viewed?
   - How many theses are downloaded?
   - How many search queries are performed?

4. **Time-Based Analysis:**
   - When are users most active?
   - What are the usage trends over time?
   - What is the daily activity pattern?

5. **Role Comparison:**
   - How do librarians, faculty, and students differ in their usage?
   - Which role uploads the most theses?
   - Which role searches the most?

---

## 📈 Metrics Available

### Individual Metrics (Per User):
| Metric | What It Shows |
|--------|---------------|
| Total Activities | How many actions the user performed |
| Total Logins | How many times they logged in |
| Avg Session Duration | Average time spent per session (in minutes) |
| Thesis Uploads | Number of theses they uploaded |
| Thesis Views | Number of theses they viewed |
| Thesis Downloads | Number of theses they downloaded |
| Search Queries | Number of searches performed |
| Most Active Day | Date with highest activity count |

### Combined Metrics (All Users):
| Metric | What It Shows |
|--------|---------------|
| Total Users Tracked | Number of librarians + faculty + students |
| Total Activities | Sum of all activities from all users |
| Avg Activities/User | Total activities ÷ total users |
| Total Logins | Sum of all logins from all users |
| Avg Session Time | Average session duration across all users |
| System Usage Score | 0-100 score based on engagement |
| Role Breakdown | Activity comparison by role |
| Common Activities | Most frequent actions |
| Time Trends | Activity over the last 30 days |

---

## 🔒 Who Can Access These Reports?

### Individual User Reports:
- ✅ **Admin** - Can view any user's report
- ✅ **Faculty** - Can view any user's report
- ✅ **Librarian** - Can view any user's report
- ❌ **Students** - Cannot access

### Combined Reports:
- ✅ **Admin** - Full access
- ✅ **Faculty** - Full access
- ✅ **Librarian** - Full access
- ❌ **Students** - Cannot access

---

## 🧪 Testing the System

### Test Individual Report:
1. Login as admin (admin@pcc.edu.ph / password)
2. Go to **Manage Users**
3. Find a student, faculty, or librarian
4. Click **"View Activity Report"**
5. Check if their data appears

### Test Combined Report:
1. Login as admin
2. Click your name → **"All Users Activity (SOP Data)"**
3. Check if aggregated statistics appear
4. Try exporting to CSV

### Test CSV Export:
1. Open the combined report
2. Click **"Export All User Data to CSV"**
3. Open the CSV in Excel
4. Verify data is readable and complete

---

## 💡 Tips for SOP Analysis

### 1. **Collect Data Over Time**
- Let the system run for at least 2-4 weeks
- More data = better analysis
- Track before and after improvements

### 2. **Calculate Statistics**
In Excel/SPSS, you can calculate:
- Mean, median, mode
- Standard deviation
- Frequency distributions
- Correlations
- T-tests, ANOVA

### 3. **Create Visualizations**
Use the CSV data to create:
- Bar charts (activities by role)
- Line charts (trends over time)
- Pie charts (activity type distribution)
- Histograms (session duration distribution)

### 4. **Answer Research Questions**
Map your SOP questions to the data:
- "How effective is the system?" → Look at usage scores and activity counts
- "Do users engage with theses?" → Check view/download counts
- "Which user group benefits most?" → Compare role breakdown
- "Is the system user-friendly?" → Check session durations and login frequency

---

## 🗂️ File Structure

| File | Purpose |
|------|---------|
| `controllers/UserActivityController.php` | Handles report generation and CSV exports |
| `views/admin/individual_report.php` | Individual user report page |
| `views/admin/combined_report.php` | Combined (all users) report page |
| `helpers/ActivityTracker.php` | Logs user activities |
| Routes in `index.php` | Maps URLs to controller methods |

---

## 🔄 How Data Collection Works

### Automatic Tracking:
When users perform actions, the system automatically logs:
- **Login** → Records in `user_sessions` table
- **Logout** → Updates session duration
- **View Thesis** → Logs as `thesis_view` activity
- **Download Thesis** → Logs as `thesis_download` activity
- **Upload Thesis** → Logs as `thesis_upload` activity
- **Search** → Logs as `thesis_search` activity

### Database Tables:
- `user_activities` - Stores individual user actions
- `user_sessions` - Stores login/logout times
- `user_statistics` - Stores aggregated stats per user

---

## ⚡ Quick Commands

### Access Individual Report:
```
http://localhost:8000/index.php?route=admin/individual-report&user_id=2
```

### Access Combined Report:
```
http://localhost:8000/index.php?route=admin/combined-report
```

### Export Individual CSV:
```
http://localhost:8000/index.php?route=admin/export-individual-csv&user_id=2
```

### Export Combined CSV:
```
http://localhost:8000/index.php?route=admin/export-combined-csv
```

---

## ✅ Summary

✅ **Individual Reports** - View each user's activity separately
✅ **Combined Reports** - View all users' data together (for SOP)
✅ **CSV Export** - Export data for statistical analysis
✅ **Only Tracks** - Librarians, Faculty, Students (not Admin)
✅ **Accessible By** - Admin, Faculty, Librarian
✅ **Perfect for** - SOP analysis, research questions, thesis documentation

**Your activity tracking system is ready to collect data for your research!** 📊✨

Hard refresh your browser and start exploring:
- **Windows:** `Ctrl + Shift + R`
- **Mac:** `Cmd + Shift + R`
