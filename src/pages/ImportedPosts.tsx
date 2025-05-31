import { Box, Typography, Card, CardContent } from '@mui/material';
import { DataGrid } from '@mui/x-data-grid';

const columns = [
  { field: 'title', headerName: 'Title', flex: 2 },
  { field: 'feed', headerName: 'Feed', flex: 1 },
  { field: 'status', headerName: 'Status', width: 130 },
  { field: 'importDate', headerName: 'Import Date', width: 180 },
  { field: 'rewritten', headerName: 'Rewritten', width: 100 },
  { field: 'published', headerName: 'Published', width: 100 },
];

const sampleData = [
  {
    id: 1,
    title: 'Sample Imported Post',
    feed: 'Tech News',
    status: 'Success',
    importDate: '2024-01-10 15:30',
    rewritten: 'Yes',
    published: 'Yes',
  },
];

export function ImportedPosts() {
  return (
    <Box>
      <Typography variant="h4" gutterBottom>
        Imported Posts
      </Typography>

      <Card>
        <CardContent>
          <DataGrid
            rows={sampleData}
            columns={columns}
            autoHeight
            initialState={{
              pagination: { paginationModel: { pageSize: 25 } },
            }}
            pageSizeOptions={[25, 50, 100]}
          />
        </CardContent>
      </Card>
    </Box>
  );
}