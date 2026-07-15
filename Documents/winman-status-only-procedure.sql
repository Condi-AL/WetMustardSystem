/*
  WinMan status-only stored procedure for DBMTS post-issue call.
  Purpose: transition ManufacturingOrders.SystemType to 'I' without issuing any materials.

  Return codes:
    0  = success (updated to I)
    1  = no-op (already I, or state not eligible)
   -1  = not found / stale / error
*/
CREATE OR ALTER PROCEDURE dbo.bsp_ManufacturingOrdersSetIssuedStatus
    @ManufacturingOrder BIGINT,
    @UserName NVARCHAR(128),
    @LastModifiedDate DATETIME = NULL
AS
BEGIN
    SET NOCOUNT ON;

    BEGIN TRY
        IF @ManufacturingOrder IS NULL OR @ManufacturingOrder <= 0
            RETURN -1;

        IF @UserName IS NULL OR LTRIM(RTRIM(@UserName)) = ''
            SET @UserName = 'DBMTS';

        IF NOT EXISTS (
            SELECT 1
            FROM ManufacturingOrders mo
            WHERE mo.ManufacturingOrder = @ManufacturingOrder
              AND (@LastModifiedDate IS NULL OR mo.LastModifiedDate = @LastModifiedDate)
        )
            RETURN -1;

        UPDATE mo
        SET mo.SystemType = 'I',
            mo.LastModifiedUser = @UserName,
            mo.LastModifiedDate = GETDATE()
        FROM ManufacturingOrders mo
        WHERE mo.ManufacturingOrder = @ManufacturingOrder
          AND mo.SystemType IN ('F', 'R')
          AND mo.QuantityOutstanding > 0
          AND (@LastModifiedDate IS NULL OR mo.LastModifiedDate = @LastModifiedDate);

        IF @@ROWCOUNT = 0
            RETURN 1;

        RETURN 0;
    END TRY
    BEGIN CATCH
        RETURN -1;
    END CATCH
END
GO
