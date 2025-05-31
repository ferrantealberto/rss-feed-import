import { useState } from 'react';
import {
  Box,
  Typography,
  Card,
  CardContent,
  Button,
  TextField,
  Grid,
  MenuItem,
} from '@mui/material';
import { DataGrid } from '@mui/x-data-grid';

const columns = [
  { field: 'name', headerName: 'Feed Name', flex: 1 },
  { field: 'url', headerName: 'URL', flex: 2 },
  { field: 'status', headerName: 'Status', width: 130 },
  { field: 'lastImport', headerName: 'Last Import', width: 180 },
  { field: 'postsCount', headerName: 'Posts', width: 100 },
];

const sampleData = [
  {
    id: 1,
    name: 'Tech News',
    url: 'https://example.com/feed',
    status: 'Active',
    lastImport: '2024-01-10 15:30',
    postsCount: 42,
  },
];

export function FeedManager() {
  const [formData, setFormData] = useState({
    name: '',
    url: '',
    frequency: 'daily',
    status: 'active',
  });

  return (
    <Box>
      <Typography variant="h4" gutterBottom>
        Feed Manager
      </Typography>

      <Card sx={{ mb: 4 }}>
        <CardContent>
          <Typography variant="h6" gutterBottom>
            Add New Feed
          </Typography>
          <Grid container spacing={2}>
            <Grid item xs={12} md={3}>
              <TextField
                label="Feed Name"
                fullWidth
                value={formData.name}
                onChange={(e) => setFormData({ ...formData, name: e.target.value })}
              />
            </Grid>
            <Grid item xs={12} md={4}>
              <TextField
                label="Feed URL"
                fullWidth
                value={formData.url}
                onChange={(e) => setFormData({ ...formData, url: e.target.value })}
              />
            </Grid>
            <Grid item xs={12} md={2}>
              <TextField
                select
                label="Frequency"
                fullWidth
                value={formData.frequency}
                onChange={(e) => setFormData({ ...formData, frequency: e.target.value })}
              >
                <MenuItem value="hourly">Hourly</MenuItem>
                <MenuItem value="daily">Daily</MenuItem>
                <MenuItem value="weekly">Weekly</MenuItem>
              </TextField>
            </Grid>
            <Grid item xs={12} md={2}>
              <TextField
                select
                label="Status"
                fullWidth
                value={formData.status}
                onChange={(e) => setFormData({ ...formData, status: e.target.value })}
              >
                <MenuItem value="active">Active</MenuItem>
                <MenuItem value="inactive">Inactive</MenuItem>
              </TextField>
            </Grid>
            <Grid item xs={12} md={1}>
              <Button
                variant="contained"
                color="primary"
                fullWidth
                sx={{ height: '100%' }}
                onClick={() => {
                  // Handle save
                }}
              >
                Add Feed
              </Button>
            </Grid>
          </Grid>
        </CardContent>
      </Card>

      <Card>
        <CardContent>
          <Box display="flex" justifyContent="space-between" alignItems="center" mb={2}>
            <Typography variant="h6">Managed Feeds</Typography>
            <Button variant="contained" color="secondary">
              Import All Active
            </Button>
          </Box>
          <DataGrid
            rows={sampleData}
            columns={columns}
            autoHeight
            initialState={{
              pagination: { paginationModel: { pageSize: 10 } },
            }}
            pageSizeOptions={[10, 25, 50]}
          />
        </CardContent>
      </Card>
    </Box>
  );
}